<?php
/**
 *
 * Maintenance extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 stoker - https://phpbb3bbcodes.com/
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

declare(strict_types=1);

namespace stoker\maintenance\acp;

class main_info
{
	/**
	 * @return array
	 */
	public function module(): array
	{
		return [
			'filename'	=> '\stoker\maintenance\acp\main_module',
			'title'		=> 'ACP_STOKER_MAINTENANCE_TITLE',
			'modes'		=> [
				'settings' => [
					'title'	=> 'ACP_STOKER_MAINTENANCE_SETTINGS',
					'auth'	=> 'ext_stoker/maintenance && acl_a_board',
					'cat'	=> ['ACP_STOKER_MAINTENANCE_TITLE'],
				],
			],
		];
	}
}
