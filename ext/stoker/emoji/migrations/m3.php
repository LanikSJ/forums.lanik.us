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
 * Emoji V2 migration: drop the unused posting_order index
 */
class m3 extends \phpbb\db\migration\migration
{
	/**
	 * @return bool
	 */
	public function effectively_installed(): bool
	{
		return !$this->db_tools->sql_index_exists($this->table_prefix . 'stoker_emoji', 'posting_order');
	}

	/**
	 * @return array
	 */
	public static function depends_on()
	{
		return ['\stoker\emoji\migrations\m2'];
	}

	/**
	 * Drop the unused (display_on_posting, emoji_order) index. No query filters
	 * on display_on_posting, so it only cost write time on every emoji save.
	 *
	 * @return array
	 */
	public function update_schema()
	{
		return [
			'drop_keys' => [
				$this->table_prefix . 'stoker_emoji' => [
					'posting_order',
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
			'add_index' => [
				$this->table_prefix . 'stoker_emoji' => [
					'posting_order' => ['display_on_posting', 'emoji_order'],
				],
			],
		];
	}
}
