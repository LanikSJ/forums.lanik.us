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

class v1_0_0_install extends \phpbb\db\migration\migration
{
	public static function depends_on(): array
	{
		return [];
	}

	public function effectively_installed(): bool
	{
		if (!$this->db_tools->sql_table_exists($this->table_prefix . 'webauthn_credentials'))
		{
			return false;
		}

		$result    = $this->db->sql_query(
			"SELECT module_id FROM " . $this->table_prefix . "modules
			 WHERE module_class = 'ucp'
			   AND module_langname = 'UCP_WEBAUTHN_TITLE'"
		);
		$module_id = $this->db->sql_fetchfield('module_id');
		$this->db->sql_freeresult($result);

		return $module_id !== false;
	}

	public function update_schema(): array
	{
		return [
			'add_tables' => [

				$this->table_prefix . 'webauthn_credentials' => [
					'COLUMNS' => [
						'credential_id' => ['VCHAR:255', ''],
						'user_id'       => ['UINT', 0],
						'public_key'    => ['TEXT_UNI', ''],
						'sign_count'    => ['UINT', 0],
						'aaguid'        => ['VCHAR:36', ''],
						'name'          => ['VCHAR:255', ''],
						'created'       => ['TIMESTAMP', 0],
						'last_used'     => ['TIMESTAMP', 0],
					],
					'PRIMARY_KEY' => 'credential_id',
					'KEYS'        => [
						'wa_user_id' => ['INDEX', 'user_id'],
					],
				],

				$this->table_prefix . 'webauthn_challenges' => [
					'COLUMNS' => [
						'challenge' => ['VCHAR:64', ''],
						'user_id'   => ['UINT', 0],
						'type'      => ['VCHAR:20', 'authenticate'],
						'created'   => ['TIMESTAMP', 0],
					],
					'PRIMARY_KEY' => 'challenge',
					'KEYS'        => [
						'wa_ch_created' => ['INDEX', 'created'],
					],
				],

			],
		];
	}

	public function revert_schema(): array
	{
		return [
			'drop_tables' => [
				$this->table_prefix . 'webauthn_credentials',
				$this->table_prefix . 'webauthn_challenges',
			],
		];
	}

	public function update_data(): array
	{
		return [
			['module.add', [
				'ucp',
				0,
				'UCP_WEBAUTHN_TITLE',
			]],
			['module.add', [
				'ucp',
				'UCP_WEBAUTHN_TITLE',
				[
					'module_basename' => '\dmzx\webauthn\ucp\main_module',
					'modes'           => ['passkeys'],
				],
			]],
		];
	}

	public function revert_data(): array
	{
		return [
			['module.remove', [
				'ucp',
				'UCP_WEBAUTHN_TITLE',
				[
					'module_basename' => '\dmzx\webauthn\ucp\main_module',
					'modes'           => ['passkeys'],
				],
			]],
			['module.remove', ['ucp', 0, 'UCP_WEBAUTHN_TITLE']],
		];
	}
}
