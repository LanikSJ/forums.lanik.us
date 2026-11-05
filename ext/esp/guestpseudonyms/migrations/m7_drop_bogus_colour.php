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
 * Drop the unused bogus_colour column.
 *
 * m1 created it alongside the pseudonym, intending pseudonymous users to be
 * able to carry their own colour. Nothing ever read it: every colour a guest
 * sees comes from the real account, through get_username_string(). It has only
 * ever held an empty string, so there is nothing to preserve.
 *
 * A new migration rather than an edit to m1 — a released migration must not be
 * altered.
 */
class m7_drop_bogus_colour extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return !$this->db_tools->sql_column_exists($this->table_prefix . 'pseudonyms', 'bogus_colour');
	}

	public static function depends_on()
	{
		return ['\esp\guestpseudonyms\migrations\m6_signature'];
	}

	public function update_schema()
	{
		return [
			'drop_columns' => [
				$this->table_prefix . 'pseudonyms' => ['bogus_colour'],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'add_columns' => [
				$this->table_prefix . 'pseudonyms' => [
					'bogus_colour' => ['VCHAR:6', ''],
				],
			],
		];
	}
}
