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
 * Emoji V2 migration: emoji table
 */
class m1 extends \phpbb\db\migration\migration
{
	/**
	 * @return bool
	 */
	public function effectively_installed(): bool
	{
		return $this->db_tools->sql_table_exists($this->table_prefix . 'stoker_emoji');
	}

	/**
	 * @return array
	 */
	public static function depends_on()
	{
		return ['\phpbb\db\migration\data\v330\v330'];
	}

	/**
	 * @return array
	 */
	public function update_schema()
	{
		return [
			'add_tables' => [
				$this->table_prefix . 'stoker_emoji' => [
					'COLUMNS' => [
						'emoji_id'			=> ['ULINT', null, 'auto_increment'],
						'emoji_char'		=> ['VCHAR_UNI', ''],
						'emoji_codes'		=> ['VCHAR_UNI', ''],
						'emoji_emotion'		=> ['VCHAR_UNI', ''],
						'emoji_order'		=> ['UINT', 0],
						'display_on_posting'=> ['BOOL', 1],
					],
					'PRIMARY_KEY' => 'emoji_id',
					'KEYS' => [
						'emoji_order'		=> ['INDEX', ['emoji_order']],
						'posting_order'		=> ['INDEX', ['display_on_posting', 'emoji_order']],
					],
				],
			],
		];
	}

	/**
	 * @return array
	 */
	public function revert_schema()
	{
		return [
			'drop_tables' => [
				$this->table_prefix . 'stoker_emoji',
			],
		];
	}
}
