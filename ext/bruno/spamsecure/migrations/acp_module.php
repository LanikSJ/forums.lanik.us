<?php
/**
*
* @package phpBB Extension - Spamsecure from 69bruno
* @copyright (c) 2021 (cruiser-lounge.de)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace bruno\spamsecure\migrations;

class acp_module extends \phpbb\db\migration\migration
{

	public static function depends_on()
	{
		return ['\phpbb\db\migration\data\v32x\v3210'];
	}

	public function update_data()
	{
		return [
			['module.add', [
				'acp',
				'ACP_CAT_DOT_MODS',
				'SPSE_TITLE'
			]],
			['module.add', [
				'acp',
				'SPSE_TITLE',
				[
					'module_basename'	=> '\bruno\spamsecure\acp\main_module',
					'modes'				=> ['settings'],
				],
			]],
		];
	}
}
