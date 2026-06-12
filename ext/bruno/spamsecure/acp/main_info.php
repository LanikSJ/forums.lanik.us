<?php
/**
*
* @package phpBB Extension - Spamsecure from 69bruno
* @copyright (c) 2021 (cruiser-lounge.de)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace bruno\spamsecure\acp;

class main_info
{
	public function module()
	{
		return [
			'filename'	=>	'\bruno\spamsecure\acp\main_module',
			'title'		=>	'SPSE_TITLE',
			'modes'		=> [
				'settings'		=>	[
					'title'		=>	'SPSE_SETTINGS',
					'auth'		=>	'ext_bruno/spamsecure && acl_a_extensions',
					'cat'		=>	['SPSE_TITLE'],
				],
			],
		];
	}
}
