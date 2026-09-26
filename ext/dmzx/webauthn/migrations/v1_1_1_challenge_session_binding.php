<?php
/**
 *
 * WebAuthn. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 dmzx, https://www.dmzx-web.net
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace dmzx\webauthn\migrations;

class v1_1_1_challenge_session_binding extends \phpbb\db\migration\migration
{
	public static function depends_on(): array
	{
		return ['\dmzx\webauthn\migrations\v1_1_0_acp_settings'];
	}

	public function effectively_installed(): bool
	{
		return $this->db_tools->sql_column_exists($this->table_prefix . 'webauthn_challenges', 'session_id');
	}

	public function update_schema(): array
	{
		return [
			'add_columns' => [
				$this->table_prefix . 'webauthn_challenges' => [
					'session_id' => ['VCHAR:32', ''],
				],
			],
			'change_columns' => [
				$this->table_prefix . 'webauthn_credentials' => [
					'credential_id' => ['VCHAR:512', ''],
				],
			],
		];
	}

	public function revert_schema(): array
	{
		return [
			'drop_columns' => [
				$this->table_prefix . 'webauthn_challenges' => [
					'session_id',
				],
			],
			'change_columns' => [
				$this->table_prefix . 'webauthn_credentials' => [
					'credential_id' => ['VCHAR:255', ''],
				],
			],
		];
	}
}
