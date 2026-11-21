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

namespace stoker\topicban\migrations;

class install_mcp_module extends \phpbb\db\migration\migration
{
	/**
	 * @return array
	 */
	public static function depends_on(): array
	{
		return ['\stoker\topicban\migrations\install_permissions'];
	}

	/**
	 * @return array
	 */
	public function update_data(): array
	{
		return [
			['module.add', ['mcp', 0, 'MCP_TOPICBAN']],
			['module.add', ['mcp', 'MCP_TOPICBAN', [
				'module_basename'	=> '\stoker\topicban\mcp\main_module',
				'modes'				=> ['bans'],
			]]],
		];
	}
}
