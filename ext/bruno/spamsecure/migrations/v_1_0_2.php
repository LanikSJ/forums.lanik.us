<?php
/**
*
* @package phpBB Extension - Spamsecure from 69bruno
* @copyright (c) 2021 (cruiser-lounge.de)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace bruno\spamsecure\migrations;

class v_1_0_2 extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\bruno\spamsecure\migrations\v_1_0_1'];
	}

	public function update_data()
	{
		return [
			['config.add', 		['spamsecure_invalid_chars_counter_switch', 0]],
			['config.add',		['spamsecure_invalid_chars_counter', '1']],
			['config.add',		['spamsecure_invalid_chars_lang_kyrill', 0]],
			['config.add',		['spamsecure_invalid_chars_lang_chinese', 0]],
			['config.add',		['spamsecure_invalid_chars_lang_hindi', 0]],
			['config.add',		['spamsecure_invalid_chars_lang_custom', 0]],
			['config.add',		['spamsecure_invalid_chars_input_custom',	$this->config['spamsecure_invalid_chars'] ]],
			['config.remove',	['spamsecure_invalid_chars']],
		];
	}

}
