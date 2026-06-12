<?php
/**
*
* @package Extension .yml Check Extension
* @copyright (c) 2019 david63
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace david63\extservicescheck\migrations;

use phpbb\db\migration\migration;

class version_2_1_0 extends migration
{
	public function update_data()
	{
		return array(
			array('module.add', array(
				'acp', 'ACP_EXTENSION_MANAGEMENT', array(
					'module_basename'	=> '\david63\extservicescheck\acp\acp_extservicescheck_module',
					'modes'				=> array('main'),
				),
			)),
		);
	}
}
