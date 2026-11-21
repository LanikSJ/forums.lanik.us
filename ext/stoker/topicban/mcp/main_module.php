<?php
/**
 *
 * Topic Ban extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 stoker - https://phpbb3bbcodes.com/
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

declare(strict_types=1);

namespace stoker\topicban\mcp;

/**
 * MCP module
 */
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

		/** @var \stoker\topicban\controller\mcp_controller $controller */
		$controller = $phpbb_container->get('stoker.topicban.controller.mcp');

		$this->tpl_name = 'mcp_topicban_body';
		$this->page_title = 'MCP_TOPICBAN_LIST';

		$controller->display($this->u_action);
	}
}
