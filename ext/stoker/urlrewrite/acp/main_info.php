<?php
/**
 *
 * URL Rewrite extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 stoker - https://phpbb3bbcodes.com/
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

declare(strict_types=1);

namespace stoker\urlrewrite\acp;

class main_info
{
	/**
	 * @return array
	 */
	public function module(): array
	{
		return [
			'filename'	=> '\stoker\urlrewrite\acp\main_module',
			'title'		=> 'ACP_URLREWRITE_TITLE',
			'modes'		=> [
				'settings'	=> [
					'title'	=> 'ACP_URLREWRITE_SETTINGS',
					'auth'	=> 'ext_stoker/urlrewrite && acl_a_board',
					'cat'	=> ['ACP_URLREWRITE_TITLE'],
				],
				'server'	=> [
					'title'	=> 'ACP_URLREWRITE_SERVER',
					'auth'	=> 'ext_stoker/urlrewrite && acl_a_board',
					'cat'	=> ['ACP_URLREWRITE_TITLE'],
				],
			],
		];
	}
}
