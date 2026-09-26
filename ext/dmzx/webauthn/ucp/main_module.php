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

class main_module
{
	public $page_title;
	public $tpl_name;
	public $u_action;

	public function main($id, $mode)
	{
		global $phpbb_container;

		$ucp_controller = $phpbb_container->get('dmzx.webauthn.ucp.controller');

		$language = $phpbb_container->get('language');
		$language->add_lang('webauthn', 'dmzx/webauthn');

		$this->tpl_name		= 'ucp_webauthn_body';
		$this->page_title	= 'WEBAUTHN_MANAGE_TITLE';

		$ucp_controller->main($this->u_action);
	}
}
