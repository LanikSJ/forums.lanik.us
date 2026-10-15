<?php
/**
 * Aurora Tech TFA — TOTP Two Factor Authentication extension for phpBB.
 *
 * French (Canada) language pack — ACP module titles.
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
	'ACP_AURORA_TFA_TITLE'		=> 'Authentification à deux facteurs',
	'ACP_AURORA_TFA_SETTINGS'	=> 'Paramètres',
	'ACP_AURORA_TFA_STATS'		=> 'Statistiques',
	'ACP_AURORA_TFA_AUDIT'		=> 'Journal d’audit',

	'LOG_AURORA_TFA_CONFIG'					=> '<strong>Politique d’authentification à deux facteurs mise à jour</strong>',
	'LOG_AURORA_TFA_ENABLED'				=> '<strong>Authentification à deux facteurs activée</strong>',
	'LOG_AURORA_TFA_DISABLED'				=> '<strong>Authentification à deux facteurs désactivée</strong>',
	'LOG_AURORA_TFA_CODES_REGENERATED'		=> '<strong>Codes de secours d’authentification à deux facteurs régénérés</strong>',
	'LOG_AURORA_TFA_LOGIN_FAILED'			=> '<strong>Échec de l’authentification à deux facteurs à l’ouverture de session</strong>',
]);
