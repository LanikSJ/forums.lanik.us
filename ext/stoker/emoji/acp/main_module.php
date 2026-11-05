<?php
/**
 *
 * Emoji V2 extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 stoker - https://phpbb3bbcodes.com/
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

declare(strict_types=1);

namespace stoker\emoji\acp;

/**
 * Emoji V2 ACP module
 */
class main_module
{
	/** @var string */
	public $u_action;

	/** @var string */
	public $tpl_name;

	/** @var string */
	public $page_title;

	/**
	 * @param string	$id
	 * @param string	$mode
	 * @return void
	 */
	public function main($id, $mode): void
	{
		global $phpbb_container;

		$controller = $phpbb_container->get('stoker.emoji.acp_controller');
		$controller->set_page_url($this->u_action);

		$language = $phpbb_container->get('language');

		if ($mode === 'emojis')
		{
			$this->tpl_name = 'acp_emojis_body';
			$this->page_title = $language->lang('ACP_STOKER_EMOJI_MANAGE');
			$controller->display_emojis();
			return;
		}

		$this->tpl_name = 'acp_emoji_body';
		$this->page_title = $language->lang('ACP_STOKER_EMOJI_SETTINGS');
		$controller->display_settings();
	}
}
