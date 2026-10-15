<?php
/**
 * Aurora Tech TFA — TOTP Two Factor Authentication extension for phpBB.
 *
 * Portuguese (Brazil) language pack — ACP module titles.
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
	'ACP_AURORA_TFA_TITLE'		=> 'Autenticação de dois fatores',
	'ACP_AURORA_TFA_SETTINGS'	=> 'Configurações',
	'ACP_AURORA_TFA_STATS'		=> 'Estatísticas',
	'ACP_AURORA_TFA_AUDIT'		=> 'Registro de auditoria',

	'LOG_AURORA_TFA_CONFIG'					=> '<strong>Política de autenticação de dois fatores atualizada</strong>',
	'LOG_AURORA_TFA_ENABLED'				=> '<strong>Autenticação de dois fatores ativada</strong>',
	'LOG_AURORA_TFA_DISABLED'				=> '<strong>Autenticação de dois fatores desativada</strong>',
	'LOG_AURORA_TFA_CODES_REGENERATED'		=> '<strong>Códigos de backup de autenticação de dois fatores gerados novamente</strong>',
	'LOG_AURORA_TFA_LOGIN_FAILED'			=> '<strong>Falha na autenticação de dois fatores no login</strong>',
]);
