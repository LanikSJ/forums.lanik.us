<?php
/**
 *
 * @package Log Connections
 * @copyright (c) 2017 david63
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace david63\logconnections\migrations;

use phpbb\db\migration\migration;

class version_2_1_0 extends migration
{
	public function update_data()
	{
		return [
			['config.add', ['log_browser', false]],
			['config.add', ['log_connect_failed', true]],
			['config.add', ['log_connect_logout', false]],
			['config.add', ['log_connect_new_user', true]],
			['config.add', ['log_connect_user', true]],

			// Add the ACP module
			['module.add', ['acp', 'ACP_CAT_DOT_MODS', 'LOG_CONNECTIONS']],

			['module.add', [
				'acp', 'LOG_CONNECTIONS', [
					'module_basename' => '\david63\logconnections\acp\logconnections_module',
					'modes' => ['manage'],
				],
			]],
		];
	}
}
