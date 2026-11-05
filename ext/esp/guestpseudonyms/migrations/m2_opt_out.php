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
 * Story #22 — opt-out of pseudonymisation.
 *
 * Adds the per-user opt-out flag (and the timestamp used by the reminder, #24)
 * to the lookup table, plus the ACP-configurable switch that allows opt-out at
 * all. Opt-out is strictly opt-in-to-reveal: everyone is protected by default.
 */
class m2_opt_out extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_column_exists($this->table_prefix . 'pseudonyms', 'opted_out');
	}

	public static function depends_on()
	{
		return ['\esp\guestpseudonyms\migrations\m1_install_schema'];
	}

	public function update_schema()
	{
		return [
			'add_columns' => [
				$this->table_prefix . 'pseudonyms' => [
					'opted_out'            => ['BOOL', 0],
					'opt_out_confirmed_at' => ['TIMESTAMP', 0],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_columns' => [
				$this->table_prefix . 'pseudonyms' => ['opted_out', 'opt_out_confirmed_at'],
			],
		];
	}

	public function update_data()
	{
		return [
			['config.add', ['guestpseudonyms_allow_opt_out', 1]],
		];
	}

	public function revert_data()
	{
		return [
			['config.remove', ['guestpseudonyms_allow_opt_out']],
		];
	}
}
