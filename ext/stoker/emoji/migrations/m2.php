<?php
/**
 *
 * Emoji V2 extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 stoker - https://phpbb3bbcodes.com/
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

declare(strict_types=1);

namespace stoker\emoji\migrations;

/**
 * Emoji V2 migration: config, ACP modules and default emoji set
 */
class m2 extends \phpbb\db\migration\migration
{
	/**
	 * @return array
	 */
	public static function depends_on()
	{
		return ['\stoker\emoji\migrations\m1'];
	}

	/**
	 * @return array
	 */
	public function update_data()
	{
		return [
			['config.add', ['stoker_emoji_mode', 0]],
			['config.add', ['stoker_emoji_size', '1.3']],
			['config.add', ['stoker_emoji_noto_size', '1.2']],
			['config.add', ['stoker_quickreply_emoji', 1]],

			['module.add', ['acp', 'ACP_CAT_DOT_MODS', 'ACP_STOKER_EMOJI_TITLE']],
			['module.add', ['acp', 'ACP_STOKER_EMOJI_TITLE', [
				'module_basename'	=> '\stoker\emoji\acp\main_module',
				'modes'				=> ['settings', 'emojis'],
			]]],

			['custom', [[$this, 'add_default_emojis']]],
		];
	}

	/**
	 * Restore the core smilies setting the extension disables in emoji modes.
	 *
	 * @return array
	 */
	public function revert_data()
	{
		return [
			['config.update', ['allow_smilies', 1]],
		];
	}

	/**
	 * Seed the default emoji set (skipped if rows already exist).
	 *
	 * @return void
	 */
	public function add_default_emojis(): void
	{
		$table = $this->table_prefix . 'stoker_emoji';

		$sql = 'SELECT COUNT(emoji_id) AS total FROM ' . $table;
		$result = $this->db->sql_query($sql);
		$total = (int) $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);

		if ($total > 0)
		{
			return;
		}

		// Pre-encoded as decimal NCR so storage is consistent regardless of
		// utf8_encode_ucr()'s 4-byte-only scope (codepoints below U+10000 are
		// 3-byte UTF-8 and would otherwise be stored as raw Unicode).
		// Format: emoji_char => [codes, emotion]
		$defaults = [
			'&#128522;'			=> [':-) :)',		'Happy'],
			'&#128516;'			=> [':-D :D',		'Very happy'],
			'&#128521;'			=> [';-) ;)',		'Wink'],
			'&#128542;'			=> [':-( :(',		'Sad'],
			'&#128539;'			=> [':-P :P',		'Tongue'],
			'&#128558;'			=> [':-O :O',		'Surprised'],
			'&#128526;'			=> ['B-) B)',		'Cool'],
			'&#128520;'			=> ['>:-) >:)',		'Evil'],
			'&#128536;'			=> [':-* :*',		'Kiss'],
			'&#128546;'			=> [":'-( :'(",		'Crying'],
			'&#128557;'			=> [':crying:',		'Crying'],
			'&#129315;'			=> ['xD XD',		'Laughing'],
			'&#128564;'			=> [':tired:',		'Tired'],
			'&#128530;'			=> [':notfunny:',	'Not funny'],
			'&#10084;&#65039;'	=> ['<3',			'Love'],
			'&#128175;'			=> [':100:',		'100%'],
			'&#10024;'			=> [':sparkles:',	'Sparkles'],
			'&#128064;'			=> [':eyes:',		'Eyes'],
			'&#128073;'			=> [':finger:',		'Pointing'],
			'&#129313;'			=> [':clown:',		'Clown'],
			'&#9749;'			=> [':coffee:',		'Coffee'],
			'&#127866;'			=> [':beer:',		'Beer'],
			'&#127870;'			=> [':champagne:',	'Champagne'],
			'&#127865;'			=> [':drink:',		'Drink'],
			'&#9728;&#65039;'	=> [':sun:',		'Sun'],
		];

		$rows = [];
		$order = 0;

		foreach ($defaults as $char => $entry)
		{
			$rows[] = [
				'emoji_char'			=> $char,
				'emoji_codes'			=> $entry[0],
				'emoji_emotion'			=> $entry[1],
				'emoji_order'			=> ++$order,
				'display_on_posting'	=> 1,
			];
		}

		$this->db->sql_multi_insert($table, $rows);
	}
}
