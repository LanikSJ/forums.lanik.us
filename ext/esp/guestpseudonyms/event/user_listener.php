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

/**
 * Story #4 (B3) — keep the lookup table in step with the user list: give every
 * newly registered member a nickname up front (in the board default language),
 * and drop the row when a user is deleted.
 */
class user_listener implements EventSubscriberInterface
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var pseudonym_manager */
	protected $manager;

	/** @var string */
	protected $table;

	public function __construct(\phpbb\db\driver\driver_interface $db, pseudonym_manager $manager, $table_prefix)
	{
		$this->db = $db;
		$this->manager = $manager;
		$this->table = $table_prefix . 'pseudonyms';
	}

	public static function getSubscribedEvents()
	{
		return [
			'core.user_add_after'    => 'create_pseudonym',
			'core.delete_user_after' => 'delete_pseudonyms',
			'core.update_username'   => 'free_renamed_username',
		];
	}

	public function create_pseudonym($event)
	{
		$user_id = (int) $event['user_id'];
		if ($user_id > 0 && $user_id !== ANONYMOUS)
		{
			// Generates and stores a nickname in the board default language.
			$this->manager->get_bogus($user_id);

			$user_row = $event['user_row'];
			if (!empty($user_row['username']))
			{
				$this->free_username($user_row['username'], $user_id);
			}
		}
	}

	/**
	 * A rename can take a username that is already somebody's nickname.
	 *
	 * @param \phpbb\event\data $event
	 */
	public function free_renamed_username($event)
	{
		$this->free_username((string) $event['new_name'], 0);
	}

	/**
	 * Make sure no other member is still wearing this name as their nickname.
	 *
	 * Nicknames are checked against both tables when they are generated, but
	 * nothing stopped a member registering later — or being renamed — to a
	 * name already issued as somebody else's nickname. Guests would then see
	 * two different people under one name. Whoever holds it as a nickname is
	 * given a fresh one; the real username always wins, since it is the name
	 * its owner actually chose.
	 *
	 * @param string $username Real username now in use.
	 * @param int    $user_id  The member taking that name, excluded from the sweep.
	 */
	protected function free_username($username, $user_id)
	{
		$clean = utf8_clean_string($username);
		if ($clean === '')
		{
			return;
		}

		$sql = 'SELECT user_id
			FROM ' . $this->table . "
			WHERE bogus_username_clean = '" . $this->db->sql_escape($clean) . "'
				AND user_id <> " . (int) $user_id;
		$result = $this->db->sql_query($sql);
		$clashing = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$clashing[] = (int) $row['user_id'];
		}
		$this->db->sql_freeresult($result);

		foreach ($clashing as $id)
		{
			$this->manager->regenerate($id);
		}
	}

	public function delete_pseudonyms($event)
	{
		$user_ids = array_map('intval', (array) $event['user_ids']);
		if (!empty($user_ids))
		{
			$this->db->sql_query('DELETE FROM ' . $this->table . '
				WHERE ' . $this->db->sql_in_set('user_id', $user_ids));
		}
	}
}
