<?php
/**
 *
 * Maintenance extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 stoker - https://phpbb3bbcodes.com/
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

declare(strict_types=1);

namespace stoker\maintenance\acp;

class main_module
{
	/** @var string */
	public $page_title;

	/** @var string */
	public $tpl_name;

	/** @var string */
	public $u_action;

	/**
	 * @return void
	 */
	public function main(): void
	{
		global $phpbb_container;

		/** @var \stoker\maintenance\controller\acp_controller $controller */
		$controller = $phpbb_container->get('stoker.maintenance.controller.acp_controller');
		$controller->set_page_url($this->u_action);

		$this->tpl_name   = 'acp_maintenance';
		$this->page_title = 'ACP_STOKER_MAINTENANCE_TITLE';

		$controller->display_settings();
	}
}
