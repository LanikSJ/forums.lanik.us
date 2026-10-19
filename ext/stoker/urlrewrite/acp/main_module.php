<?php
/**
 *
 * URL Rewrite extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 stoker - https://phpbb3bbcodes.com/
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

declare(strict_types=1);

namespace stoker\urlrewrite\acp;

class main_module
{
	/** @var string */
	public $u_action;

	/** @var string */
	public $tpl_name;

	/** @var string */
	public $page_title;

	/**
	 * @param string $id
	 * @param string $mode
	 * @return void
	 */
	public function main($id, $mode): void
	{
		global $phpbb_container;

		$mode = ($mode === 'server') ? 'server' : 'settings';

		/** @var \stoker\urlrewrite\controller\acp_controller $controller */
		$controller = $phpbb_container->get('stoker.urlrewrite.controller.acp');
		$controller->set_page_url($this->u_action);

		$this->tpl_name = 'acp_urlrewrite_' . $mode;

		if ($mode === 'server')
		{
			$controller->handle_server();
		}
		else
		{
			$controller->handle_settings();
		}

		$this->page_title = $controller->get_page_title();
	}
}
