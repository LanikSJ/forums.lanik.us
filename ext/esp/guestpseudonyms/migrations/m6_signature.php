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
 * 1.3.0 — signatures join the per-member privacy choice, and the guest/bot
 * split is dropped.
 *
 * Adds a `show_signature` flag to the lookup table. Together with `opted_out`
 * it gives members a single three-way choice for what non-logged-in visitors
 * (guests and search-engine bots alike) see:
 *
 *   Level 1 (default)  opted_out=0, show_signature=0  pseudonym, signature hidden
 *   Level 2            opted_out=1, show_signature=0  real name, signature hidden
 *   Level 3            opted_out=1, show_signature=1  real name, signature shown
 *
 * The contradictory combination (hidden name but visible signature) is never
 * written. Existing opted-out members are mapped to Level 3 — exactly the real
 * name + signature they have now; everyone else stays at Level 1, which means
 * their signature is now hidden from guests (previously it leaked).
 *
 * Also removes guestpseudonyms_apply_to_bots: treating bots differently from
 * guests let a member's choice fail to hide them from search engines, which
 * defeats the point. Non-logged-in viewers are now handled identically.
 */
class m6_signature extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_column_exists($this->table_prefix . 'pseudonyms', 'show_signature');
	}

	public static function depends_on()
	{
		return [
			'\esp\guestpseudonyms\migrations\m2_opt_out',
			'\esp\guestpseudonyms\migrations\m4_acp_module',
		];
	}

	public function update_schema()
	{
		return [
			'add_columns' => [
				$this->table_prefix . 'pseudonyms' => [
					'show_signature' => ['BOOL', 0],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_columns' => [
				$this->table_prefix . 'pseudonyms' => ['show_signature'],
			],
		];
	}

	public function update_data()
	{
		return [
			// Members who already reveal their real name keep their signature
			// visible too (Level 3) — their current state, unchanged.
			['custom', [[$this, 'map_existing_opt_outs']]],
			// Guests and bots are treated identically from now on.
			['config.remove', ['guestpseudonyms_apply_to_bots']],
		];
	}

	public function revert_data()
	{
		return [
			['config.add', ['guestpseudonyms_apply_to_bots', 1]],
		];
	}

	public function map_existing_opt_outs()
	{
		$this->db->sql_query('UPDATE ' . $this->table_prefix . 'pseudonyms
			SET show_signature = 1
			WHERE opted_out = 1');
	}
}
