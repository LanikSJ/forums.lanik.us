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
 * Cover the corners of viewtopic that read a cached username directly instead
 * of going through get_username_string().
 *
 * The username hook catches every rendered author link, but viewtopic also
 * keeps a raw 'username' per poster in $user_cache and prints it verbatim in
 * the "Last bumped by …" line. Two separate things populate that cache:
 *
 *   - Posters, through core.viewtopic_cache_user_data, which this listener
 *     disguises as each entry is built.
 *   - Whoever edited or deleted a post, added later in the render loop with no
 *     event of its own. Those are pre-seeded here, already disguised, before
 *     the loop runs: core only fills an entry that is not already set, so
 *     seeding it first is enough to keep the real name out.
 *
 * Both paths also prime the pseudonym cache in bulk. Without that a disguised
 * viewer costs one query per distinct member on the page.
 */
class viewtopic_listener implements EventSubscriberInterface
{
	/** @var viewer */
	protected $viewer;

	/** @var pseudonym_manager */
	protected $manager;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var string */
	protected $users_table;

	/** @var int[] Poster ids seen while the post rowset was built. */
	protected $seen = [];

	/** @var bool Whether $seen has been primed yet this request. */
	protected $primed = false;

	/**
	 * @param viewer                            $viewer
	 * @param pseudonym_manager                 $manager
	 * @param \phpbb\db\driver\driver_interface $db
	 * @param string                            $table_prefix
	 */
	public function __construct(viewer $viewer, pseudonym_manager $manager, \phpbb\db\driver\driver_interface $db, $table_prefix)
	{
		$this->viewer = $viewer;
		$this->manager = $manager;
		$this->db = $db;
		$this->users_table = $table_prefix . 'users';
	}

	/**
	 * @return array
	 */
	public static function getSubscribedEvents()
	{
		return [
			'core.viewtopic_post_rowset_data' => 'collect_poster',
			'core.viewtopic_cache_user_data'  => 'disguise_cached_username',
			'core.viewtopic_modify_post_data' => 'seed_editors_and_deleters',
		];
	}

	/**
	 * Note each poster as the rowset is assembled, so the whole page's
	 * pseudonyms can be loaded in one query when the first one is needed.
	 *
	 * @param \phpbb\event\data $event
	 */
	public function collect_poster($event)
	{
		if (!$this->viewer->is_disguised())
		{
			return;
		}

		$row = $event['row'];
		if (!empty($row['poster_id']))
		{
			$this->seen[] = (int) $row['poster_id'];
		}
	}

	/**
	 * Replace the raw username kept in viewtopic's poster cache with that
	 * poster's pseudonym, for disguised viewers.
	 *
	 * @param \phpbb\event\data $event
	 */
	public function disguise_cached_username($event)
	{
		if (!$this->viewer->is_disguised())
		{
			return;
		}

		$poster_id = (int) $event['poster_id'];
		if ($poster_id <= 0 || $poster_id === ANONYMOUS)
		{
			return;
		}

		// First entry of the page: load every poster's pseudonym at once.
		if (!$this->primed)
		{
			$this->primed = true;
			if (!empty($this->seen))
			{
				$this->manager->prime($this->seen);
			}
		}

		$user_cache_data = $event['user_cache_data'];
		if (empty($user_cache_data['username']))
		{
			return;
		}

		$bogus = $this->manager->get_pseudonym($poster_id);
		if ($bogus === '')
		{
			return;
		}

		$user_cache_data['username'] = $bogus;
		$event['user_cache_data'] = $user_cache_data;
	}

	/**
	 * Seed disguised cache entries for anyone who edited or deleted a post on
	 * this page.
	 *
	 * viewtopic adds those to $user_cache inside the render loop without
	 * firing an event, and the "Last bumped by …" line is built from the same
	 * cache a few lines later — so a bumper who appears on the page only as an
	 * editor was shown under their real name. Core fills such an entry only
	 * when it is not already set, so seeding it here, before the loop, is what
	 * keeps the real name out; there is no event between the two points.
	 *
	 * @param \phpbb\event\data $event
	 */
	public function seed_editors_and_deleters($event)
	{
		if (!$this->viewer->is_disguised())
		{
			return;
		}

		$user_cache = $event['user_cache'];

		$ids = [];
		foreach ($event['rowset'] as $row)
		{
			foreach (['post_edit_user', 'post_delete_user'] as $field)
			{
				$id = isset($row[$field]) ? (int) $row[$field] : 0;
				if ($id > 0 && $id !== ANONYMOUS && !isset($user_cache[$id]))
				{
					$ids[$id] = $id;
				}
			}
		}

		if (empty($ids))
		{
			return;
		}

		$this->manager->prime($ids);

		// The colour still comes from the real account — only the name is
		// disguised — so it has to be read alongside.
		$sql = 'SELECT user_id, user_colour
			FROM ' . $this->users_table . '
			WHERE ' . $this->db->sql_in_set('user_id', $ids);
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$id = (int) $row['user_id'];
			$bogus = $this->manager->get_pseudonym($id);

			if ($bogus !== '')
			{
				$user_cache[$id] = [
					'user_id'     => $id,
					'username'    => $bogus,
					'user_colour' => $row['user_colour'],
				];
			}
		}
		$this->db->sql_freeresult($result);

		$event['user_cache'] = $user_cache;
	}
}
