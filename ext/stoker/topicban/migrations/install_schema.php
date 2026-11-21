<?php
/**
 *
 * Topic Ban extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 stoker - https://phpbb3bbcodes.com/
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

declare(strict_types=1);

namespace stoker\topicban\migrations;

class install_schema extends \phpbb\db\migration\migration
{
	/**
	 * @return array
	 */
	public static function depends_on(): array
	{
		return ['\phpbb\db\migration\data\v330\v330'];
	}

	/**
	 * @return array
	 */
	public function update_schema(): array
	{
		return [
			'add_tables' => [
				$this->table_prefix . 'stoker_topic_bans' => [
					'COLUMNS' => [
						'topic_id'		=> ['ULINT', 0],
						'user_id'		=> ['ULINT', 0],
						'banned_by'		=> ['ULINT', 0],
						'ban_time'		=> ['TIMESTAMP', 0],
						'ban_end'		=> ['TIMESTAMP', 0],
						'ban_reason'	=> ['VCHAR_UNI', ''],
					],
					'PRIMARY_KEY' => ['topic_id', 'user_id'],
					'KEYS' => [
						'user_id'	=> ['INDEX', 'user_id'],
						'ban_end'	=> ['INDEX', 'ban_end'],
					],
				],
			],
		];
	}

	/**
	 * @return array
	 */
	public function revert_schema(): array
	{
		return [
			'drop_tables' => [
				$this->table_prefix . 'stoker_topic_bans',
			],
		];
	}

	/**
	 * @return array
	 */
	public function update_data(): array
	{
		return [
			['config.add', ['stoker_topicban_last_purge', 0, true]],
			['config.add', ['stoker_topicban_cache_version', 0, true]],
		];
	}
}
