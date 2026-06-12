<?php
/**
*
* @package phpBB Extension - Spamsecure from 69bruno
* @copyright (c) 2021 (cruiser-lounge.de)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace bruno\spamsecure\migrations;

class permission extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\bruno\spamsecure\migrations\acp_module'];
	}

	public function update_data()
	{
		return [
			['permission.add', ['u_view_spamsecure']],
		];
	}
}
