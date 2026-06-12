<?php
/**
*
* @package phpBB Extension - Spamsecure from 69bruno
* @copyright (c) 2021 (cruiser-lounge.de)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace bruno\spamsecure\migrations;

class v_1_0_1 extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\bruno\spamsecure\migrations\v_1_0_0'];
	}

	public function update_data()
	{
		return [
			['permission.remove',	['u_view_spamsecure']],
			['permission.add',		['u_view_spamsecure_invalid_regex']],
			['permission.add',		['u_view_spamsecure_invalid_chars']],
		];
	}

}
