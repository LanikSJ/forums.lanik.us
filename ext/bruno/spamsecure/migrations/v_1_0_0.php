<?php
/**
*
* @package phpBB Extension - Spamsecure from 69bruno
* @copyright (c) 2021 (cruiser-lounge.de)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace bruno\spamsecure\migrations;

class v_1_0_0 extends \phpbb\db\migration\migration
{

	public static function depends_on()
	{
		return ['\bruno\spamsecure\migrations\permission'];
	}

	public function update_data()
	{
		return [
			['config.add', ['spamsecure_invalid_chars', '']],
			['config.add', ['spamsecure_invalid_regex', '']],
		];
	}
}
