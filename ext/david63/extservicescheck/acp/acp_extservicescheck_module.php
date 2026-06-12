<?php
/**
*
* @package Extension .yml Check Extension
* @copyright (c) 2019 david63
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace david63\extservicescheck\acp;

class acp_extservicescheck_module
{
	public $u_action;

	function main($id, $mode)
	{
		global $phpbb_container;

		$this->tpl_name		= 'extservicescheck';
		$this->page_title	= $phpbb_container->get('language')->lang('EXT_SERVICES_CHECK');

		// Get an instance of the admin controller
		$admin_controller = $phpbb_container->get('david63.extservicescheck.admin.controller');

		// Make the $u_action url available in the data controller
		$admin_controller->set_page_url($this->u_action);

		$admin_controller->display_output();
	}
}
