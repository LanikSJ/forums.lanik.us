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

class v1_1_0_acp_settings extends \phpbb\db\migration\migration
{
	public static function depends_on(): array
	{
		return ['\dmzx\webauthn\migrations\v1_0_0_install'];
	}

	public function effectively_installed(): bool
	{
		return isset($this->config['dmzx_webauthn_enabled']);
	}

	public function update_data(): array
	{
		return [
			['config.add', ['dmzx_webauthn_enabled', 1]],
			['config.add', ['dmzx_webauthn_remember_me_mode', 'off']],
			['config.add', ['dmzx_webauthn_user_verification', 'preferred']],
			['config.add', ['dmzx_webauthn_max_keys', 5]],

			['module.add', [
				'acp',
				'ACP_CAT_DOT_MODS',
				'ACP_WEBAUTHN_TITLE',
			]],
			['module.add', [
				'acp',
				'ACP_WEBAUTHN_TITLE',
				[
					'module_basename' => '\dmzx\webauthn\acp\main_module',
					'modes'           => ['settings'],
				],
			]],
		];
	}

	public function revert_data(): array
	{
		return [
			['module.remove', [
				'acp',
				'ACP_WEBAUTHN_TITLE',
				[
					'module_basename' => '\dmzx\webauthn\acp\main_module',
					'modes'           => ['settings'],
				],
			]],
			['module.remove', ['acp', 'ACP_CAT_DOT_MODS', 'ACP_WEBAUTHN_TITLE']],

			['config.remove', ['dmzx_webauthn_enabled']],
			['config.remove', ['dmzx_webauthn_remember_me_mode']],
			['config.remove', ['dmzx_webauthn_user_verification']],
			['config.remove', ['dmzx_webauthn_max_keys']],
		];
	}
}
