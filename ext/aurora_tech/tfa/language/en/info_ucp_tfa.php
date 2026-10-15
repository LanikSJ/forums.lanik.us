<?php
/**
 * Aurora Tech TFA — TOTP Two Factor Authentication extension for phpBB.
 *
 * English (International) language pack — UCP module titles.
 *
 * @copyright (c) 2026 Aurora Tech Inc.
 * @license GPL-2.0-only
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
	'UCP_AURORA_TFA_TITLE'	=> 'Two Factor Authentication',
	'UCP_AURORA_TFA_MANAGE'	=> 'Manage Two Factor Authentication',
]);
