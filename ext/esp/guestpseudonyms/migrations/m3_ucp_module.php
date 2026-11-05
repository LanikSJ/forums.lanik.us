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
 * Story #23 — register the UCP "Guest privacy" module and the ACP-configurable
 * switch that allows members to regenerate their own nickname.
 */
class m3_ucp_module extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['guestpseudonyms_allow_regenerate']);
	}

	public static function depends_on()
	{
		return ['\esp\guestpseudonyms\migrations\m2_opt_out'];
	}

	public function update_data()
	{
		return [
			['config.add', ['guestpseudonyms_allow_regenerate', 1]],
			['module.add', [
				'ucp',
				'UCP_PROFILE',
				[
					'module_basename' => '\esp\guestpseudonyms\ucp\main_module',
					'modes'           => ['settings'],
				],
			]],
		];
	}

	public function revert_data()
	{
		return [
			['config.remove', ['guestpseudonyms_allow_regenerate']],
			['module.remove', [
				'ucp',
				'UCP_PROFILE',
				[
					'module_basename' => '\esp\guestpseudonyms\ucp\main_module',
				],
			]],
		];
	}
}
