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
 * Emoji V2 ACP module info
 */
class main_info
{
	/**
	 * @return array
	 */
	public function module()
	{
		return [
			'filename'	=> '\stoker\emoji\acp\main_module',
			'title'		=> 'ACP_STOKER_EMOJI_TITLE',
			'modes'		=> [
				'settings'	=> [
					'title'	=> 'ACP_STOKER_EMOJI_SETTINGS',
					'auth'	=> 'ext_stoker/emoji && acl_a_board',
					'cat'	=> ['ACP_STOKER_EMOJI_TITLE'],
				],
				'emojis'	=> [
					'title'	=> 'ACP_STOKER_EMOJI_MANAGE',
					'auth'	=> 'ext_stoker/emoji && acl_a_board',
					'cat'	=> ['ACP_STOKER_EMOJI_TITLE'],
				],
			],
		];
	}
}
