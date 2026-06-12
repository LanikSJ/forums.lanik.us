<?php
/**
*
* @package phpBB Extension - Spamsecure from 69bruno
* @copyright (c) 2021 (cruiser-lounge.de)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace bruno\spamsecure\migrations;

class v_1_0_6 extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\bruno\spamsecure\migrations\v_1_0_5'];
	}

	public function update_data()
	{
		return [
			['config.add', 		['spamsecure_search_complete', 0]],
		];
	}

}
