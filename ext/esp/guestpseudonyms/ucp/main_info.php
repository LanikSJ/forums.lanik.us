<?php
/**
 *
 * Guest pseudonyms. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Emma Laurijssens van Engelenhoven
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace esp\guestpseudonyms\ucp;

/**
 * UCP module metadata for the "Guest privacy" page (Story #23).
 */
class main_info
{
	public function module()
	{
		return [
			'filename' => 'esp\guestpseudonyms\ucp\main_module',
			'title'    => 'UCP_GUESTPRIVACY',
			'modes'    => [
				'settings' => [
					'title' => 'UCP_GUESTPRIVACY',
					'auth'  => 'ext_esp/guestpseudonyms',
					'cat'   => ['UCP_PROFILE'],
				],
			],
		];
	}
}
