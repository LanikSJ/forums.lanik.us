<?php
/**
 * Aurora Tech TFA — TOTP Two Factor Authentication extension for phpBB.
 *
 * @copyright (c) 2026 Aurora Tech Inc.
 * @license GPL-2.0-only
 */

namespace aurora_tech\tfa\migrations;

/**
 * M5: policy configuration and ACP module (settings, statistics, audit).
 */
class release_1_0_0_m5_acp_module extends \phpbb\db\migration\migration
{
	/**
	 * Declare the migration chain.
	 *
	 * @return string[] Fully qualified migration class names.
	 */
	public static function depends_on()
	{
		return ['\aurora_tech\tfa\migrations\release_1_0_0_m4_challenge'];
	}

	/**
	 * Seed policy configuration and register the ACP module.
	 *
	 * @return array<int, array<int, mixed>> phpBB data changes.
	 */
	public function update_data()
	{
		return [
			// Default policy: optional (users may enrol, nothing forced).
			['config.add', ['aurora_tfa_mode', '1']],
			['config_text.add', ['aurora_tfa_required_groups', '[]']],

			['module.add', ['acp', 'ACP_CAT_DOT_MODS', 'ACP_AURORA_TFA_TITLE']],
			['module.add', [
				'acp', 'ACP_AURORA_TFA_TITLE', [
					'module_basename'	=> '\aurora_tech\tfa\acp\main_module',
					'modes'				=> ['settings', 'stats', 'audit'],
				],
			]],
		];
	}
}
