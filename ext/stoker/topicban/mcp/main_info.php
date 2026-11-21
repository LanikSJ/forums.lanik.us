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
 * MCP module info
 */
class main_info
{
	/**
	 * @return array
	 */
	public function module(): array
	{
		return [
			'filename'	=> '\stoker\topicban\mcp\main_module',
			'title'		=> 'MCP_TOPICBAN',
			'modes'		=> [
				'bans'	=> [
					'title'	=> 'MCP_TOPICBAN_LIST',
					'auth'	=> 'ext_stoker/topicban && aclf_m_topicban',
					'cat'	=> ['MCP_TOPICBAN'],
				],
			],
		];
	}
}
