<?php
/**
 *
 * WebAuthn. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 dmzx, https://www.dmzx-web.net
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace dmzx\webauthn\ucp;

class main_info
{
	public function module()
	{
		return [
			'filename'	=> '\dmzx\webauthn\ucp\main_module',
			'title'		=> 'UCP_WEBAUTHN_TITLE',
			'modes'		=> [
				'passkeys'	=> [
					'title'	=> 'WEBAUTHN_MANAGE_TITLE',
					'auth'	=> 'ext_dmzx/webauthn',
					'cat'	=> ['UCP_WEBAUTHN_TITLE'],
				],
			],
		];
	}
}
