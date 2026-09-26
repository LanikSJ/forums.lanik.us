<?php
/**
 *
 * WebAuthn. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 dmzx, https://www.dmzx-web.net
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

$lang = array_merge($lang, [
	'UCP_WEBAUTHN_TITLE'	=> 'Passkeys',
	'WEBAUTHN_MANAGE_TITLE'	=> 'Manage passkeys',
]);
