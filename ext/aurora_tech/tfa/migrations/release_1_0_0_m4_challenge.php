<?php
/**
 * Aurora Tech TFA — TOTP Two Factor Authentication extension for phpBB.
 *
 * @copyright (c) 2026 Aurora Tech Inc.
 * @license GPL-2.0-only
 */

namespace aurora_tech\tfa\migrations;

/**
 * M4 schema: pending login challenges.
 *
 * A dedicated table keyed by the pre-login session id — deliberately NOT
 * extra columns on the core sessions table, so a purge of the extension
 * leaves the phpBB core schema untouched.
 */
class release_1_0_0_m4_challenge extends \phpbb\db\migration\migration
{
	/**
	 * Skip when the schema already exists.
	 *
	 * @return bool True when the challenge table is already present.
	 */
	public function effectively_installed()
	{
		return $this->db_tools->sql_table_exists($this->table_prefix . 'aurora_tfa_challenge');
	}

	/**
	 * Declare the migration chain.
	 *
	 * @return string[] Fully qualified migration class names.
	 */
	public static function depends_on()
	{
		return ['\aurora_tech\tfa\migrations\release_1_0_0_m3_ucp_module'];
	}

	/**
	 * Create the challenge table.
	 *
	 * @return array<string, mixed> phpBB schema changes.
	 */
	public function update_schema()
	{
		return [
			'add_tables'	=> [
				$this->table_prefix . 'aurora_tfa_challenge'	=> [
					'COLUMNS'	=> [
						'session_id'	=> ['CHAR:32', ''],
						'user_id'		=> ['ULINT', 0],
						'is_admin'		=> ['BOOL', 0],
						'auto_login'	=> ['BOOL', 0],
						'viewonline'	=> ['BOOL', 1],
						'token'			=> ['VCHAR:64', ''],
						'redirect_url'	=> ['VCHAR:255', ''],
						'created_at'	=> ['TIMESTAMP', 0],
					],
					'PRIMARY_KEY'	=> 'session_id',
				],
			],
		];
	}

	/**
	 * Drop the challenge table on purge.
	 *
	 * @return array<string, mixed> phpBB schema changes.
	 */
	public function revert_schema()
	{
		return [
			'drop_tables'	=> [
				$this->table_prefix . 'aurora_tfa_challenge',
			],
		];
	}
}
