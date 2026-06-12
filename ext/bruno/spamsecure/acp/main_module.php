<?php
/**
*
* @package phpBB Extension - Spamsecure from 69bruno
* @copyright (c) 2021 (cruiser-lounge.de)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/
namespace bruno\spamsecure\acp;

class main_module
{
	public $page_title;
	public $tpl_name;
	public $u_action;

	public function main()
	{
		global $phpbb_container;

		$this->tpl_name = 'acp_spamsecure_settings';

		$acp_controller = $phpbb_container->get('bruno.spamsecure.controller.acp');

		$language = $phpbb_container->get('language');

		$this->page_title = $language->lang('SPSE_TITLE');

		$acp_controller->set_page_url($this->u_action);

		$acp_controller->display_options();
	}
}
