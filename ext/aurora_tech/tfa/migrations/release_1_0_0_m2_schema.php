<?php
/**
 * Aurora Tech TFA — TOTP Two Factor Authentication extension for phpBB.
 *
 * @copyright (c) 2026 Aurora Tech Inc.
 * @license GPL-2.0-only
 */

namespace aurora_tech\tfa\migrations;

/**
 * M2 schema: TOTP enrolment and backup-code tables.
 *
 * Secrets are stored encrypted (see service\secret_cipher); backup codes
 * are stored as password hashes only.
 */
class release_1_0_0_m2_schema extends \phpbb\db\migration\migration
{
	/**
	 * Skip when the schema already exists.
	 *
	 * @return bool True when the TOTP table is already present.
	 */
	public function effectively_installed()
	{
		return $this->db_tools->sql_table_exists($this->table_prefix . 'aurora_tfa_totp');
	}

	/**
	 * Declare the migration chain.
	 *
	 * @return string[] Fully qualified migration class names.
	 */
	public static function depends_on()
	{
		return ['\aurora_tech\tfa\migrations\release_1_0_0_dev'];
	}

	/**
	 * Create the extension tables.
	 *
	 * @return array<string, mixed> phpBB schema changes.
	 */
	public function update_schema()
	{
		return [
			'add_tables'	=> [
				$this->table_prefix . 'aurora_tfa_totp'	=> [
					'COLUMNS'	=> [
						'user_id'			=> ['ULINT', 0],
						'secret_encrypted'	=> ['VCHAR:255', ''],
						'activated_at'		=> ['TIMESTAMP', 0],
						'last_used_at'		=> ['TIMESTAMP', 0],
					],
					'PRIMARY_KEY'	=> 'user_id',
				],
				$this->table_prefix . 'aurora_tfa_backup_codes'	=> [
					'COLUMNS'	=> [
						'code_id'	=> ['UINT', null, 'auto_increment'],
						'user_id'	=> ['ULINT', 0],
						'code_hash'	=> ['VCHAR:255', ''],
						'used_at'	=> ['TIMESTAMP', 0],
					],
					'PRIMARY_KEY'	=> 'code_id',
					'KEYS'		=> [
						'user_id'	=> ['INDEX', 'user_id'],
					],
				],
			],
		];
	}

	/**
	 * Drop the extension tables on purge.
	 *
	 * @return array<string, mixed> phpBB schema changes.
	 */
	public function revert_schema()
	{
		return [
			'drop_tables'	=> [
				$this->table_prefix . 'aurora_tfa_totp',
				$this->table_prefix . 'aurora_tfa_backup_codes',
			],
		];
	}

	/**
	 * Seed configuration: encryption key placeholder (generated lazily on
	 * first encryption by service\secret_cipher).
	 *
	 * @return array<int, array<int, mixed>> phpBB data changes.
	 */
	public function update_data()
	{
		return [
			['config.add', ['aurora_tfa_encryption_key', '']],
		];
	}
}
