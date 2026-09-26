<?php
/**
 *
 * WebAuthn. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 dmzx, https://www.dmzx-web.net
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace dmzx\webauthn;

class ext extends \phpbb\extension\base
{
	public function is_enableable()
	{
		return version_compare(PHP_VERSION, '8.0.0', '>=');
	}
}
