<?php
/**
 * Aurora Tech TFA — TOTP Two Factor Authentication extension for phpBB.
 *
 * @copyright (c) 2026 Aurora Tech Inc.
 * @license GPL-2.0-only
 */

namespace aurora_tech\tfa\acp;

/**
 * ACP module descriptor.
 */
class main_info
{
	/**
	 * Describe the ACP module and its modes.
	 *
	 * @return array<string, mixed> phpBB module description.
	 */
	public function module()
	{
		return [
			'filename'	=> '\aurora_tech\tfa\acp\main_module',
			'title'		=> 'ACP_AURORA_TFA_TITLE',
			'modes'		=> [
				'settings'	=> [
					'title'	=> 'ACP_AURORA_TFA_SETTINGS',
					'auth'	=> 'ext_aurora_tech/tfa && acl_a_board',
					'cat'	=> ['ACP_AURORA_TFA_TITLE'],
				],
				'stats'		=> [
					'title'	=> 'ACP_AURORA_TFA_STATS',
					'auth'	=> 'ext_aurora_tech/tfa && acl_a_board',
					'cat'	=> ['ACP_AURORA_TFA_TITLE'],
				],
				'audit'		=> [
					'title'	=> 'ACP_AURORA_TFA_AUDIT',
					'auth'	=> 'ext_aurora_tech/tfa && acl_a_board',
					'cat'	=> ['ACP_AURORA_TFA_TITLE'],
				],
			],
		];
	}
}
