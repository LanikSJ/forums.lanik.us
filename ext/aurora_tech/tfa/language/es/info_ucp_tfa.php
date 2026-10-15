<?php
/**
 * Aurora Tech TFA — TOTP Two Factor Authentication extension for phpBB.
 *
 * Spanish (formal — "usted") language pack — UCP module titles.
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
	'UCP_AURORA_TFA_TITLE'	=> 'Autenticación de dos factores',
	'UCP_AURORA_TFA_MANAGE'	=> 'Gestionar la autenticación de dos factores',
]);
