<?php
/**
 *
 * Guest pseudonyms. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Emma Laurijssens van Engelenhoven
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace esp\guestpseudonyms\migrations;

/**
 * Story B1 — the bogus-name lookup table.
 *
 * Maps each user_id to a stable pseudonym shown to guests/bots. The real
 * username is never stored here; only the generated bogus identity.
 */
class m1_install_schema extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_table_exists($this->table_prefix . 'pseudonyms');
	}

	public static function depends_on()
	{
		// Left at v331, the release this migration shipped against: an existing
		// migration must not be altered once it has been released. It is not in
		// conflict with the 3.3.15 minimum either — ext::is_enableable() refuses
		// to enable below 3.3.15, and migrations only run on enable, so this
		// file can never execute on a board older than that anyway. The one
		// true minimum lives in ext::MIN_PHPBB_VERSION and composer.json.
		return ['\phpbb\db\migration\data\v33x\v331'];
	}

	public function update_schema()
	{
		return [
			'add_tables' => [
				$this->table_prefix . 'pseudonyms' => [
					'COLUMNS' => [
						'user_id'              => ['ULINT', 0],
						'bogus_username'       => ['VCHAR_UNI:255', ''],
						'bogus_username_clean' => ['VCHAR_CI', ''],
						'bogus_colour'         => ['VCHAR:6', ''],
						'created_at'           => ['TIMESTAMP', 0],
					],
					'PRIMARY_KEY' => 'user_id',
					'KEYS'        => [
						'bogus_clean' => ['UNIQUE', 'bogus_username_clean'],
					],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_tables' => [
				$this->table_prefix . 'pseudonyms',
			],
		];
	}
}
