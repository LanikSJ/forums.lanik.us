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

class main_module
{
	public $page_title;
	public $tpl_name;
	public $u_action;

	public function main($id, $mode)
	{
		global $phpbb_container;

		$acp_controller = $phpbb_container->get('dmzx.webauthn.acp.controller');

		$language = $phpbb_container->get('language');
		$language->add_lang('webauthn_acp', 'dmzx/webauthn');

		$this->tpl_name   = 'webauthn_acp_body';
		$this->page_title = 'ACP_WEBAUTHN_SETTINGS';

		$acp_controller->main($this->u_action);
	}
}
