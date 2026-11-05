<?php
/**
 *
 * Guest pseudonyms. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Emma Laurijssens van Engelenhoven
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace esp\guestpseudonyms\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use esp\guestpseudonyms\service\pseudonym_manager;
use esp\guestpseudonyms\service\viewer;

/**
 * Close the memberlist's own real-name surfaces.
 *
 * get_username_string() covers every *rendered* username, but memberlist.php
 * reads the raw username column in three places that never pass through it:
 *
 *   1. The profile page title and breadcrumb are built straight from
 *      $member['username'] (VIEWING_PROFILE / BREADCRUMB_NAME).
 *   2. memberlist.php?mode=viewprofile&un=<name> looks a member up by
 *      username_clean, so the URL itself carries (and confirms) a real name.
 *   3. The member search filters — "Find a member", the A-Z first-character
 *      strip and sorting by username — all run against username_clean. Even
 *      though the rows come back disguised, they tell a guest whether a real
 *      name exists and which letter it starts with.
 *
 * For disguised viewers every one of those is re-pointed at the name that
 * viewer actually sees: the member's pseudonym, or their real name if they
 * opted out.
 */
class memberlist_listener implements EventSubscriberInterface
{
	/** Placeholder swapped for the display-name SQL expression as the last step. */
	const NAME_TOKEN = '##GUESTPSEUDONYMS_DISPLAY_NAME##';

	/** @var viewer */
	protected $viewer;

	/** @var pseudonym_manager */
	protected $manager;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var string */
	protected $table;

	/**
	 * @param viewer                            $viewer
	 * @param pseudonym_manager                 $manager
	 * @param \phpbb\db\driver\driver_interface $db
	 * @param \phpbb\config\config              $config
	 * @param \phpbb\request\request_interface  $request
	 * @param string                            $table_prefix
	 */
	public function __construct(viewer $viewer, pseudonym_manager $manager, \phpbb\db\driver\driver_interface $db, \phpbb\config\config $config, \phpbb\request\request_interface $request, $table_prefix)
	{
		$this->viewer = $viewer;
		$this->manager = $manager;
		$this->db = $db;
		$this->config = $config;
		$this->request = $request;
		$this->table = $table_prefix . 'pseudonyms';
	}

	/**
	 * @return array
	 */
	public static function getSubscribedEvents()
	{
		return [
			'core.memberlist_modify_viewprofile_sql' => 'resolve_profile_by_display_name',
			'core.memberlist_view_profile'           => 'disguise_profile_member',
			'core.memberlist_modify_sql_query_data'  => 'disguise_member_search',
			'core.memberlist_memberrow_before'       => 'prime_member_rows',
		];
	}

	/**
	 * Load the pseudonyms for a whole member-list page in one query.
	 *
	 * Without this each of the ~25 rows resolves its own name and costs a
	 * query of its own.
	 *
	 * @param \phpbb\event\data $event
	 */
	public function prime_member_rows($event)
	{
		if (!$this->viewer->is_disguised())
		{
			return;
		}

		$user_list = $event['user_list'];
		if (!empty($user_list))
		{
			$this->manager->prime($user_list);
		}
	}

	/**
	 * ?mode=viewprofile&un=<name> resolves a member by username_clean. For a
	 * disguised viewer the only name they know is the displayed one, so look
	 * that up instead — a real username then simply finds nobody.
	 *
	 * @param \phpbb\event\data $event
	 */
	public function resolve_profile_by_display_name($event)
	{
		if (!$this->viewer->is_disguised())
		{
			return;
		}

		$username = (string) $event['username'];
		if ($username === '')
		{
			return;
		}

		$sql_array = $event['sql_array'];
		$sql_array['WHERE'] = 'u.user_id = ' . (int) $this->manager->get_user_id_by_display_name($username);
		$event['sql_array'] = $sql_array;
	}

	/**
	 * Disguise the member record before the profile page is assembled, so the
	 * page title, the breadcrumb and the lang strings built from it
	 * (VIEWING_PROFILE, SEND_EMAIL_USER, CONTACT_USER) all carry the pseudonym.
	 *
	 * @param \phpbb\event\data $event
	 */
	public function disguise_profile_member($event)
	{
		if (!$this->viewer->is_disguised())
		{
			return;
		}

		$member = $event['member'];
		$user_id = (int) $member['user_id'];

		if ($user_id <= 0 || $user_id === ANONYMOUS || empty($member['username']))
		{
			return;
		}

		$bogus = $this->manager->get_pseudonym($user_id);
		if ($bogus === '')
		{
			return;
		}

		$member['username'] = $bogus;
		$event['member'] = $member;
	}

	/**
	 * Re-point the member search, the A-Z strip and username sorting at the
	 * displayed name.
	 *
	 * phpBB builds these conditions as plain SQL strings before firing the
	 * event, so each one is rebuilt exactly as core did and swapped out. If a
	 * future phpBB builds them differently the swap will not match — in that
	 * case the query is forced to return nothing rather than fall through to a
	 * real-name search.
	 *
	 * @param \phpbb\event\data $event
	 */
	public function disguise_member_search($event)
	{
		if (!$this->viewer->is_disguised())
		{
			return;
		}

		$sql_where = $event['sql_where'];
		$order_by = $event['order_by'];
		$touched = false;

		$username = $this->request->variable('username', '', true);
		if ($username !== '')
		{
			$like = $this->db->sql_like_expression(str_replace('*', $this->db->get_any_char(), utf8_clean_string($username)));
			$sql_where = str_replace(' AND u.username_clean ' . $like, ' AND ' . self::NAME_TOKEN . ' ' . $like, $sql_where);
			$touched = true;
		}

		$first_char = $this->request->variable('first_char', '');
		if ($first_char === 'other')
		{
			for ($i = 97; $i < 123; $i++)
			{
				$like = $this->db->sql_like_expression(chr($i) . $this->db->get_any_char());
				$sql_where = str_replace(' AND u.username_clean NOT ' . $like, ' AND ' . self::NAME_TOKEN . ' NOT ' . $like, $sql_where);
			}
			$touched = true;
		}
		else if ($first_char !== '')
		{
			$like = $this->db->sql_like_expression(substr($first_char, 0, 1) . $this->db->get_any_char());
			$sql_where = str_replace(' AND u.username_clean ' . $like, ' AND ' . self::NAME_TOKEN . ' ' . $like, $sql_where);
			$touched = true;
		}

		if (strpos($order_by, 'u.username_clean') !== false)
		{
			$order_by = str_replace('u.username_clean', self::NAME_TOKEN, $order_by);
			$touched = true;
		}

		if (!$touched)
		{
			return;
		}

		// Anything still filtering on the real name means a swap above missed;
		// return an empty list rather than leak.
		if (strpos($sql_where, 'u.username_clean') !== false)
		{
			$sql_where .= ' AND u.user_id = 0';
		}

		$name = $this->display_name_expression();

		$event['sql_where'] = str_replace(self::NAME_TOKEN, $name, $sql_where);
		$event['order_by'] = str_replace(self::NAME_TOKEN, $name, $order_by);
	}

	/**
	 * SQL expression for the cleaned name a disguised viewer sees: the real one
	 * for members who opted out (while opt-out is allowed board-wide), the
	 * pseudonym otherwise. NULL for a member who has no pseudonym yet, which
	 * simply never matches a filter.
	 *
	 * Written as correlated subqueries rather than a join, because nothing may
	 * be added to core's $sql_from. In mode=group memberlist.php explodes that
	 * string on commas and spaces to recover table aliases and rebuilds the
	 * query from them, so a prepended LEFT JOIN comes back as a bogus alias and
	 * the page dies with an SQL error. The subqueries need no FROM entry at all.
	 *
	 * @return string
	 */
	protected function display_name_expression()
	{
		// The table name is the only thing substituted, and it comes from
		// %core.table_prefix%, never from a request. It goes in through
		// sprintf() rather than concatenation so that no part of this SQL is
		// built by joining strings to variables at all.
		if (empty($this->config['guestpseudonyms_allow_opt_out']))
		{
			return sprintf('(SELECT gp.bogus_username_clean FROM %1$s gp WHERE gp.user_id = u.user_id)', $this->table);
		}

		return sprintf('CASE WHEN (SELECT gp.opted_out FROM %1$s gp WHERE gp.user_id = u.user_id) = 1
			THEN u.username_clean
			ELSE (SELECT gp.bogus_username_clean FROM %1$s gp WHERE gp.user_id = u.user_id) END', $this->table);
	}
}
