<?php
/**
 *
 * Topic Ban extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 stoker - https://phpbb3bbcodes.com/
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

declare(strict_types=1);

namespace stoker\topicban\service;

use phpbb\auth\auth;
use phpbb\cache\driver\driver_interface as cache_interface;
use phpbb\config\config;
use phpbb\db\driver\driver_interface;
use phpbb\notification\manager;
use phpbb\user;
use stoker\topicban\ext;

/**
 * Topic ban storage, lookup and notification handling.
 * is_banned() is a public API for other extensions; keep its signature stable.
 */
class topicban_manager
{
	/** @var string Cache key prefix of the users with active bans, followed by the cache version */
	protected const CACHE_KEY = '_stoker_topicban_users_';

	/** @var int Maximum cache lifetime in seconds */
	protected const CACHE_TTL = 86400;

	/** @var auth */
	protected $auth;

	/** @var cache_interface */
	protected $cache_driver;

	/** @var config */
	protected $config;

	/** @var driver_interface */
	protected $db;

	/** @var manager */
	protected $notification_manager;

	/** @var user */
	protected $user;

	/** @var string */
	protected $table;

	/** @var array Per-request cache of active bans, key "topic_id:user_id" */
	protected $cache = [];

	/** @var array|null Per-request copy of the users with active bans, user_id => true */
	protected $banned_users;

	/** @var array Per-request user rows for permission checks, user_id => row */
	protected $user_rows = [];

	/** @var array Per-request protected state, key "user_id:forum_id" */
	protected $protected_users = [];

	/**
	 * @param auth             $auth
	 * @param cache_interface  $cache_driver
	 * @param config           $config
	 * @param driver_interface $db
	 * @param manager          $notification_manager
	 * @param user             $user
	 * @param string           $table
	 */
	public function __construct(auth $auth, cache_interface $cache_driver, config $config, driver_interface $db, manager $notification_manager, user $user, string $table)
	{
		$this->auth = $auth;
		$this->cache_driver = $cache_driver;
		$this->config = $config;
		$this->db = $db;
		$this->notification_manager = $notification_manager;
		$this->user = $user;
		$this->table = $table;
	}

	/**
	 * Is the user banned from posting in the topic?
	 * For the current user, admins and moderators of the topic's forum are never banned.
	 *
	 * @param int $topic_id
	 * @param int $user_id
	 * @return bool
	 */
	public function is_banned(int $topic_id, int $user_id): bool
	{
		$ban = $this->get_ban($topic_id, $user_id);

		if ($ban === null)
		{
			return false;
		}

		if ($user_id === (int) $this->user->data['user_id'] && $this->is_current_user_protected((int) $ban['forum_id']))
		{
			return false;
		}

		return true;
	}

	/**
	 * Get the active ban row, or null
	 *
	 * @param int $topic_id
	 * @param int $user_id
	 * @return array|null
	 */
	public function get_ban(int $topic_id, int $user_id): ?array
	{
		if ($topic_id <= 0 || $user_id <= 0 || $user_id === ANONYMOUS)
		{
			return null;
		}

		$key = $topic_id . ':' . $user_id;

		if (!array_key_exists($key, $this->cache))
		{
			if (!$this->user_has_bans($user_id))
			{
				$this->cache[$key] = null;

				return null;
			}

			$sql = 'SELECT b.banned_by, b.ban_time, b.ban_end, b.ban_reason, t.forum_id
				FROM ' . $this->table . ' b, ' . TOPICS_TABLE . ' t
				WHERE b.topic_id = ' . (int) $topic_id . '
					AND b.user_id = ' . (int) $user_id . '
					AND (b.ban_end = 0 OR b.ban_end > ' . time() . ')
					AND t.topic_id = b.topic_id';
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			$this->cache[$key] = $row ?: null;
		}

		return $this->cache[$key];
	}

	/**
	 * Load the active bans of a user in several topics with one query
	 *
	 * @param array $topic_ids
	 * @param int   $user_id
	 * @return void
	 */
	public function preload_bans(array $topic_ids, int $user_id): void
	{
		if ($user_id <= 0 || $user_id === ANONYMOUS || !$this->user_has_bans($user_id))
		{
			return;
		}

		$load = [];
		foreach (array_unique(array_map('intval', $topic_ids)) as $topic_id)
		{
			if ($topic_id > 0 && !array_key_exists($topic_id . ':' . $user_id, $this->cache))
			{
				$load[] = $topic_id;
				$this->cache[$topic_id . ':' . $user_id] = null;
			}
		}

		if (empty($load))
		{
			return;
		}

		$sql = 'SELECT b.topic_id, b.banned_by, b.ban_time, b.ban_end, b.ban_reason, t.forum_id
			FROM ' . $this->table . ' b, ' . TOPICS_TABLE . ' t
			WHERE ' . $this->db->sql_in_set('b.topic_id', $load) . '
				AND b.user_id = ' . (int) $user_id . '
				AND (b.ban_end = 0 OR b.ban_end > ' . time() . ')
				AND t.topic_id = b.topic_id';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$key = (int) $row['topic_id'] . ':' . $user_id;
			unset($row['topic_id']);
			$this->cache[$key] = $row;
		}
		$this->db->sql_freeresult($result);
	}

	/**
	 * Load the active bans of several users in one topic, with their permission data, in one query
	 *
	 * @param int   $topic_id
	 * @param array $user_ids
	 * @return void
	 */
	public function preload_topic_bans(int $topic_id, array $user_ids): void
	{
		if ($topic_id <= 0)
		{
			return;
		}

		$load = [];
		foreach (array_unique(array_map('intval', $user_ids)) as $user_id)
		{
			if ($user_id > 0 && $user_id !== ANONYMOUS && !array_key_exists($topic_id . ':' . $user_id, $this->cache) && $this->user_has_bans($user_id))
			{
				$load[] = $user_id;
				$this->cache[$topic_id . ':' . $user_id] = null;
			}
		}

		if (empty($load))
		{
			return;
		}

		$sql = 'SELECT b.user_id, b.banned_by, b.ban_time, b.ban_end, b.ban_reason, t.forum_id, u.user_type, u.user_permissions, u.user_perm_from
			FROM ' . $this->table . ' b, ' . TOPICS_TABLE . ' t, ' . USERS_TABLE . ' u
			WHERE b.topic_id = ' . (int) $topic_id . '
				AND ' . $this->db->sql_in_set('b.user_id', $load) . '
				AND (b.ban_end = 0 OR b.ban_end > ' . time() . ')
				AND t.topic_id = b.topic_id
				AND u.user_id = b.user_id';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$user_id = (int) $row['user_id'];

			$this->cache[$topic_id . ':' . $user_id] = [
				'banned_by'		=> $row['banned_by'],
				'ban_time'		=> $row['ban_time'],
				'ban_end'		=> $row['ban_end'],
				'ban_reason'	=> $row['ban_reason'],
				'forum_id'		=> $row['forum_id'],
			];

			$this->user_rows[$user_id] = [
				'user_id'			=> $user_id,
				'user_type'			=> $row['user_type'],
				'user_permissions'	=> $row['user_permissions'],
				'user_perm_from'	=> $row['user_perm_from'],
			];
		}
		$this->db->sql_freeresult($result);
	}

	/**
	 * Is the user an administrator or a moderator of the forum?
	 * Unknown users and bots are treated as protected.
	 *
	 * @param int        $user_id
	 * @param int        $forum_id
	 * @param array|null $user_row User row with user_id, user_type and user_permissions, skips the query
	 * @return bool
	 */
	public function is_protected_user(int $user_id, int $forum_id, ?array $user_row = null): bool
	{
		if ($user_id === (int) $this->user->data['user_id'])
		{
			return $this->is_current_user_protected($forum_id);
		}

		$key = $user_id . ':' . $forum_id;

		if (isset($this->protected_users[$key]))
		{
			return $this->protected_users[$key];
		}

		$row = $user_row;

		if ($row === null || !isset($row['user_id'], $row['user_type'], $row['user_permissions']) || (int) $row['user_id'] !== $user_id)
		{
			if (!array_key_exists($user_id, $this->user_rows))
			{
				$sql = 'SELECT user_id, user_type, user_permissions, user_perm_from
					FROM ' . USERS_TABLE . '
					WHERE user_id = ' . (int) $user_id;
				$result = $this->db->sql_query($sql);
				$this->user_rows[$user_id] = $this->db->sql_fetchrow($result) ?: null;
				$this->db->sql_freeresult($result);
			}

			$row = $this->user_rows[$user_id];
		}

		if (!$row || $user_id === ANONYMOUS || in_array((int) $row['user_type'], [USER_IGNORE, USER_FOUNDER], true))
		{
			$this->protected_users[$key] = true;

			return true;
		}

		$user_auth = new auth();
		$user_auth->acl($row);

		$this->protected_users[$key] = $user_auth->acl_get('a_') || $user_auth->acl_get('m_', $forum_id);

		return $this->protected_users[$key];
	}

	/**
	 * Add or update a ban
	 *
	 * @param int    $topic_id
	 * @param int    $user_id
	 * @param int    $banned_by
	 * @param int    $ban_end
	 * @param string $reason
	 * @return void
	 */
	public function ban(int $topic_id, int $user_id, int $banned_by, int $ban_end, string $reason): void
	{
		$sql_ary = [
			'banned_by'		=> $banned_by,
			'ban_time'		=> time(),
			'ban_end'		=> $ban_end,
			'ban_reason'	=> $reason,
		];

		$sql_where = ' WHERE topic_id = ' . (int) $topic_id . '
				AND user_id = ' . (int) $user_id;
		$sql_update = 'UPDATE ' . $this->table . '
			SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . $sql_where;
		$this->db->sql_query($sql_update);

		// No row yet, or MySQL reported an unchanged row
		if (!$this->db->sql_affectedrows())
		{
			$sql_insert = 'INSERT INTO ' . $this->table . ' ' . $this->db->sql_build_array('INSERT', array_merge([
				'topic_id'	=> $topic_id,
				'user_id'	=> $user_id,
			], $sql_ary));

			// The row can exist: unchanged on MySQL, or inserted by a concurrent ban
			$this->db->sql_return_on_error(true);
			$this->db->sql_query($sql_insert);
			$failed = $this->db->get_sql_error_triggered();
			$this->db->sql_return_on_error(false);

			if ($failed)
			{
				$this->db->sql_query($sql_update);

				if (!$this->db->sql_affectedrows())
				{
					$sql = 'SELECT topic_id
						FROM ' . $this->table . $sql_where;
					$result = $this->db->sql_query_limit($sql, 1);
					$exists = (bool) $this->db->sql_fetchfield('topic_id');
					$this->db->sql_freeresult($result);

					// Not a duplicate row, repeat the insert to show the error
					if (!$exists)
					{
						$this->db->sql_query($sql_insert);
					}
				}
			}
		}

		unset($this->cache[$topic_id . ':' . $user_id]);

		// After the row is written: a list built before this sees the old version and is never read again
		$old_key = $this->get_cache_key();
		$this->config->increment('stoker_topicban_cache_version', 1, false);
		$this->cache_driver->destroy($old_key);
		$this->banned_users = null;
	}

	/**
	 * Remove an active ban and its notification
	 *
	 * @param int $topic_id
	 * @param int $user_id
	 * @return bool True if an active ban was removed
	 */
	public function unban(int $topic_id, int $user_id): bool
	{
		$sql = 'DELETE FROM ' . $this->table . '
			WHERE topic_id = ' . (int) $topic_id . '
				AND user_id = ' . (int) $user_id . '
				AND (ban_end = 0 OR ban_end > ' . time() . ')';
		$this->db->sql_query($sql);
		$removed = (bool) $this->db->sql_affectedrows();

		unset($this->cache[$topic_id . ':' . $user_id]);
		$this->notification_manager->delete_notifications(ext::NOTIFICATION_TYPE, $topic_id, false, $user_id);

		if ($removed)
		{
			$this->destroy_cache();
		}

		return $removed;
	}

	/**
	 * Remove several active bans, limited to the given forums
	 *
	 * @param array $pairs     List of [topic_id, user_id]
	 * @param array $forum_ids
	 * @return array Removed rows with topic_id, user_id, forum_id, topic_title, username
	 */
	public function unban_pairs(array $pairs, array $forum_ids): array
	{
		$where = [];
		foreach ($pairs as $pair)
		{
			$where[] = '(b.topic_id = ' . (int) $pair[0] . ' AND b.user_id = ' . (int) $pair[1] . ')';
		}

		if (empty($where) || empty($forum_ids))
		{
			return [];
		}

		$sql = 'SELECT b.topic_id, b.user_id, t.forum_id, t.topic_title, u.username
			FROM ' . $this->table . ' b, ' . TOPICS_TABLE . ' t, ' . USERS_TABLE . ' u
			WHERE (' . implode(' OR ', $where) . ')
				AND t.topic_id = b.topic_id
				AND u.user_id = b.user_id
				AND ' . $this->db->sql_in_set('t.forum_id', array_map('intval', $forum_ids)) . '
				AND (b.ban_end = 0 OR b.ban_end > ' . time() . ')';
		$result = $this->db->sql_query($sql);
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		if (empty($rows))
		{
			return [];
		}

		$delete = [];
		$topics_by_user = [];
		foreach ($rows as $row)
		{
			$topic_id = (int) $row['topic_id'];
			$user_id = (int) $row['user_id'];

			$delete[] = '(topic_id = ' . $topic_id . ' AND user_id = ' . $user_id . ')';
			$topics_by_user[$user_id][] = $topic_id;
			unset($this->cache[$topic_id . ':' . $user_id]);
		}

		$sql = 'DELETE FROM ' . $this->table . '
			WHERE ' . implode(' OR ', $delete);
		$this->db->sql_query($sql);

		$this->destroy_cache();

		foreach ($topics_by_user as $user_id => $topic_ids)
		{
			$this->notification_manager->delete_notifications(ext::NOTIFICATION_TYPE, $topic_ids, false, (int) $user_id);
		}

		return $rows;
	}

	/**
	 * Count active bans in the given forums
	 *
	 * @param array $forum_ids
	 * @return int
	 */
	public function count_bans(array $forum_ids): int
	{
		$sql = 'SELECT COUNT(b.topic_id) AS total
			FROM ' . $this->table . ' b, ' . TOPICS_TABLE . ' t
			WHERE t.topic_id = b.topic_id
				AND ' . $this->db->sql_in_set('t.forum_id', array_map('intval', $forum_ids)) . '
				AND (b.ban_end = 0 OR b.ban_end > ' . time() . ')';
		$result = $this->db->sql_query($sql);
		$total = (int) $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);

		return $total;
	}

	/**
	 * Get active bans in the given forums, newest first
	 *
	 * @param array $forum_ids
	 * @param int   $start
	 * @param int   $limit
	 * @return array
	 */
	public function get_bans(array $forum_ids, int $start, int $limit): array
	{
		$sql = $this->db->sql_build_query('SELECT', [
			'SELECT'	=> 'b.topic_id, b.user_id, b.banned_by, b.ban_time, b.ban_end, b.ban_reason,
				t.topic_title, u.username, u.user_colour, bu.username AS banner_name, bu.user_colour AS banner_colour',
			'FROM'		=> [
				$this->table	=> 'b',
				TOPICS_TABLE	=> 't',
				USERS_TABLE		=> 'u',
			],
			'LEFT_JOIN'	=> [
				[
					'FROM'	=> [USERS_TABLE => 'bu'],
					'ON'	=> 'bu.user_id = b.banned_by',
				],
			],
			'WHERE'		=> 't.topic_id = b.topic_id
				AND u.user_id = b.user_id
				AND ' . $this->db->sql_in_set('t.forum_id', array_map('intval', $forum_ids)) . '
				AND (b.ban_end = 0 OR b.ban_end > ' . time() . ')',
			// Primary key as tie-breaker keeps the paging stable
			'ORDER_BY'	=> 'b.ban_time DESC, b.topic_id DESC, b.user_id DESC',
		]);
		$result = $this->db->sql_query_limit($sql, $limit, $start);
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $rows;
	}

	/**
	 * Delete expired bans and their notifications.
	 * The cached user list already excludes expired bans, it is not cleared here.
	 *
	 * @return void
	 */
	public function purge_expired(): void
	{
		$now = time();

		$sql = 'SELECT topic_id, user_id
			FROM ' . $this->table . '
			WHERE ban_end > 0
				AND ban_end <= ' . (int) $now;
		$result = $this->db->sql_query($sql);

		$topics_by_user = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$topics_by_user[(int) $row['user_id']][] = (int) $row['topic_id'];
		}
		$this->db->sql_freeresult($result);

		if (empty($topics_by_user))
		{
			return;
		}

		$sql = 'DELETE FROM ' . $this->table . '
			WHERE ban_end > 0
				AND ban_end <= ' . (int) $now;
		$this->db->sql_query($sql);

		foreach ($topics_by_user as $user_id => $topic_ids)
		{
			$this->notification_manager->delete_notifications(ext::NOTIFICATION_TYPE, $topic_ids, false, (int) $user_id);
		}
	}

	/**
	 * Delete all bans and ban notifications of deleted users
	 *
	 * @param array $user_ids
	 * @return void
	 */
	public function delete_user_bans(array $user_ids): void
	{
		if (empty($user_ids))
		{
			return;
		}

		$user_ids = array_unique(array_map('intval', $user_ids));

		$sql = 'SELECT topic_id, user_id
			FROM ' . $this->table . '
			WHERE ' . $this->db->sql_in_set('user_id', $user_ids);
		$result = $this->db->sql_query($sql);

		$topics_by_user = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$topics_by_user[(int) $row['user_id']][] = (int) $row['topic_id'];
		}
		$this->db->sql_freeresult($result);

		if (empty($topics_by_user))
		{
			return;
		}

		$sql = 'DELETE FROM ' . $this->table . '
			WHERE ' . $this->db->sql_in_set('user_id', array_keys($topics_by_user));
		$this->db->sql_query($sql);

		$this->destroy_cache();

		foreach ($topics_by_user as $user_id => $topic_ids)
		{
			$this->notification_manager->delete_notifications(ext::NOTIFICATION_TYPE, $topic_ids, false, (int) $user_id);
		}
	}

	/**
	 * Topics of the given list that have ban rows, expired ones included
	 *
	 * @param array $topic_ids
	 * @return array
	 */
	public function get_banned_topics(array $topic_ids): array
	{
		$banned = [];

		foreach (array_chunk(array_unique(array_map('intval', $topic_ids)), 500) as $chunk)
		{
			$sql = 'SELECT DISTINCT topic_id
				FROM ' . $this->table . '
				WHERE ' . $this->db->sql_in_set('topic_id', $chunk);
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$banned[] = (int) $row['topic_id'];
			}
			$this->db->sql_freeresult($result);
		}

		return $banned;
	}

	/**
	 * Delete all bans and ban notifications of the topics in a forum
	 *
	 * @param int $forum_id
	 * @return void
	 */
	public function delete_forum_bans(int $forum_id): void
	{
		$sql = 'SELECT DISTINCT b.topic_id
			FROM ' . $this->table . ' b, ' . TOPICS_TABLE . ' t
			WHERE t.topic_id = b.topic_id
				AND t.forum_id = ' . (int) $forum_id;
		$result = $this->db->sql_query($sql);

		$topic_ids = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$topic_ids[] = (int) $row['topic_id'];
		}
		$this->db->sql_freeresult($result);

		$this->delete_topic_bans($topic_ids);
	}

	/**
	 * Delete all bans and ban notifications of the given topics
	 *
	 * @param array $topic_ids
	 * @return void
	 */
	public function delete_topic_bans(array $topic_ids): void
	{
		if (empty($topic_ids))
		{
			return;
		}

		foreach (array_chunk(array_map('intval', $topic_ids), 500) as $chunk)
		{
			$sql = 'DELETE FROM ' . $this->table . '
				WHERE ' . $this->db->sql_in_set('topic_id', $chunk);
			$this->db->sql_query($sql);

			$this->notification_manager->delete_notifications(ext::NOTIFICATION_TYPE, $chunk);
		}

		$this->destroy_cache();
	}

	/**
	 * Send the ban notification, replacing an old one for the same topic
	 *
	 * @param array $data topic_id, forum_id, user_id, topic_title, ban_end, ban_reason
	 * @return void
	 */
	public function notify_ban(array $data): void
	{
		$this->notification_manager->delete_notifications(ext::NOTIFICATION_TYPE, (int) $data['topic_id'], false, (int) $data['user_id']);
		$this->notification_manager->add_notifications(ext::NOTIFICATION_TYPE, $data);
	}

	/**
	 * Update the existing ban notification without marking it unread,
	 * or send a new one if the user has none for this topic
	 *
	 * @param array $data topic_id, forum_id, user_id, topic_title, ban_end, ban_reason
	 * @return void
	 */
	public function update_ban_notification(array $data): void
	{
		$options = [
			'item_id'	=> (int) $data['topic_id'],
			'user_id'	=> (int) $data['user_id'],
		];

		if (empty($this->notification_manager->get_notified_users(ext::NOTIFICATION_TYPE, $options)))
		{
			$this->notification_manager->add_notifications(ext::NOTIFICATION_TYPE, $data);

			return;
		}

		$this->notification_manager->update_notifications(ext::NOTIFICATION_TYPE, $data, $options);
	}

	/**
	 * Clear the cached list of users with active bans
	 *
	 * @return void
	 */
	public function destroy_cache(): void
	{
		$this->cache_driver->destroy($this->get_cache_key());
		$this->banned_users = null;
	}

	/**
	 * Does the user have any active ban?
	 *
	 * @param int $user_id
	 * @return bool
	 */
	protected function user_has_bans(int $user_id): bool
	{
		if ($this->banned_users === null)
		{
			// Read the version before the bans are queried
			$cache_key = $this->get_cache_key();
			$banned_users = $this->cache_driver->get($cache_key);

			if (!is_array($banned_users))
			{
				$banned_users = [];
				$now = time();
				$ttl = self::CACHE_TTL;

				$sql = 'SELECT user_id, ban_end
					FROM ' . $this->table . '
					WHERE ban_end = 0
						OR ban_end > ' . (int) $now;
				$result = $this->db->sql_query($sql);
				while ($row = $this->db->sql_fetchrow($result))
				{
					$banned_users[(int) $row['user_id']] = true;
					$ban_end = (int) $row['ban_end'];

					// Expire the list when the first temporary ban ends
					if ($ban_end > 0)
					{
						$ttl = min($ttl, $ban_end - $now);
					}
				}
				$this->db->sql_freeresult($result);

				$this->cache_driver->put($cache_key, $banned_users, $ttl);
			}

			$this->banned_users = $banned_users;
		}

		return isset($this->banned_users[$user_id]);
	}

	/**
	 * @return string
	 */
	protected function get_cache_key(): string
	{
		return self::CACHE_KEY . (int) $this->config['stoker_topicban_cache_version'];
	}

	/**
	 * @param int $forum_id
	 * @return bool
	 */
	protected function is_current_user_protected(int $forum_id): bool
	{
		return $this->auth->acl_get('a_') || $this->auth->acl_get('m_', $forum_id);
	}
}
