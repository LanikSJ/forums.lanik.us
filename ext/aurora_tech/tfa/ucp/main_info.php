<?php
/**
 * Aurora Tech TFA — TOTP Two Factor Authentication extension for phpBB.
 *
 * @copyright (c) 2026 Aurora Tech Inc.
 * @license GPL-2.0-only
 */

namespace aurora_tech\tfa\ucp;

/**
 * UCP module descriptor.
 */
class main_info
{
	/**
	 * Describe the UCP module and its modes.
	 *
	 * @return array<string, mixed> phpBB module description.
	 */
	public function module()
	{
		return [
			'filename'	=> '\aurora_tech\tfa\ucp\main_module',
			'title'		=> 'UCP_AURORA_TFA_TITLE',
			'modes'		=> [
				'manage'	=> [
					'title'	=> 'UCP_AURORA_TFA_MANAGE',
					'auth'	=> 'ext_aurora_tech/tfa',
					'cat'	=> ['UCP_PROFILE'],
				],
			],
		];
	}
}
