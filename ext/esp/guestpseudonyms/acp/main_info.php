<?php
/**
 *
 * Guest pseudonyms. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Emma Laurijssens van Engelenhoven
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace esp\guestpseudonyms\acp;

/**
 * ACP module metadata for the Guest pseudonyms settings page (Story #14).
 */
class main_info
{
	public function module()
	{
		return [
			'filename' => 'esp\guestpseudonyms\acp\main_module',
			'title'    => 'ACP_GUESTPRIVACY_TITLE',
			'modes'    => [
				'settings' => [
					'title' => 'ACP_GUESTPRIVACY_SETTINGS',
					'auth'  => 'ext_esp/guestpseudonyms && acl_a_board',
					'cat'   => ['ACP_CAT_DOT_MODS'],
				],
			],
		];
	}
}
