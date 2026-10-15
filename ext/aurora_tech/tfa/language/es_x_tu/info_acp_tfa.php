<?php
/**
 * Aurora Tech TFA — TOTP Two Factor Authentication extension for phpBB.
 *
 * Spanish (informal — "tú") language pack — ACP module titles.
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
	'ACP_AURORA_TFA_TITLE'		=> 'Autenticación de dos factores',
	'ACP_AURORA_TFA_SETTINGS'	=> 'Ajustes',
	'ACP_AURORA_TFA_STATS'		=> 'Estadísticas',
	'ACP_AURORA_TFA_AUDIT'		=> 'Registro de auditoría',

	'LOG_AURORA_TFA_CONFIG'					=> '<strong>Política de autenticación de dos factores actualizada</strong>',
	'LOG_AURORA_TFA_ENABLED'				=> '<strong>Autenticación de dos factores activada</strong>',
	'LOG_AURORA_TFA_DISABLED'				=> '<strong>Autenticación de dos factores desactivada</strong>',
	'LOG_AURORA_TFA_CODES_REGENERATED'		=> '<strong>Códigos de respaldo de autenticación de dos factores regenerados</strong>',
	'LOG_AURORA_TFA_LOGIN_FAILED'			=> '<strong>Fallo de la autenticación de dos factores al iniciar sesión</strong>',
]);
