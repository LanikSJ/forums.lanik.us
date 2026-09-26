<?php
/**
 *
 * WebAuthn. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 dmzx, https://www.dmzx-web.net
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace dmzx\webauthn\acp;

class main_info
{
	public function module()
	{
		return [
			'filename'	=> '\dmzx\webauthn\acp\main_module',
			'title'		=> 'ACP_WEBAUTHN_TITLE',
			'modes'		=> [
				'settings'	=> [
					'title'	=> 'ACP_WEBAUTHN_SETTINGS',
					'auth'	=> 'ext_dmzx/webauthn && acl_a_board',
					'cat'	=> ['ACP_WEBAUTHN_TITLE'],
				],
			],
		];
	}
}
