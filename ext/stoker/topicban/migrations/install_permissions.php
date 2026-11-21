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

class install_permissions extends \phpbb\db\migration\migration
{
	/**
	 * @return array
	 */
	public static function depends_on(): array
	{
		return ['\stoker\topicban\migrations\install_schema'];
	}

	/**
	 * @return array
	 */
	public function update_data(): array
	{
		return [
			['permission.add', ['m_topicban', true]],
			['permission.add', ['m_topicban', false]],

			['if', [
				['permission.role_exists', ['ROLE_MOD_FULL']],
				['permission.permission_set', ['ROLE_MOD_FULL', 'm_topicban']],
			]],
			['if', [
				['permission.role_exists', ['ROLE_MOD_STANDARD']],
				['permission.permission_set', ['ROLE_MOD_STANDARD', 'm_topicban']],
			]],
		];
	}
}
