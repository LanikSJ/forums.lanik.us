<?php
/**
 * Aurora Tech TFA — TOTP Two Factor Authentication extension for phpBB.
 *
 * @copyright (c) 2026 Aurora Tech Inc.
 * @license GPL-2.0-only
 */

namespace aurora_tech\tfa\migrations;

/**
 * M3: register the UCP module under the Profile category.
 */
class release_1_0_0_m3_ucp_module extends \phpbb\db\migration\migration
{
	/**
	 * Declare the migration chain.
	 *
	 * @return string[] Fully qualified migration class names.
	 */
	public static function depends_on()
	{
		return ['\aurora_tech\tfa\migrations\release_1_0_0_m2_schema'];
	}

	/**
	 * Add the UCP module.
	 *
	 * @return array<int, array<int, mixed>> phpBB data changes.
	 */
	public function update_data()
	{
		return [
			['module.add', [
				'ucp', 'UCP_PROFILE', [
					'module_basename'	=> '\aurora_tech\tfa\ucp\main_module',
					'modes'				=> ['manage'],
				],
			]],
		];
	}
}
