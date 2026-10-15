<?php
/**
 * Aurora Tech TFA — TOTP Two Factor Authentication extension for phpBB.
 *
 * English (International) language pack — ACP module titles.
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
	'ACP_AURORA_TFA_TITLE'		=> 'Two Factor Authentication',
	'ACP_AURORA_TFA_SETTINGS'	=> 'Settings',
	'ACP_AURORA_TFA_STATS'		=> 'Statistics',
	'ACP_AURORA_TFA_AUDIT'		=> 'Audit log',

	'LOG_AURORA_TFA_CONFIG'					=> '<strong>Two factor authentication policy updated</strong>',
	'LOG_AURORA_TFA_ENABLED'				=> '<strong>Two factor authentication enabled</strong>',
	'LOG_AURORA_TFA_DISABLED'				=> '<strong>Two factor authentication disabled</strong>',
	'LOG_AURORA_TFA_CODES_REGENERATED'		=> '<strong>Two factor authentication backup codes regenerated</strong>',
	'LOG_AURORA_TFA_LOGIN_FAILED'			=> '<strong>Two factor authentication failed at login</strong>',
]);
