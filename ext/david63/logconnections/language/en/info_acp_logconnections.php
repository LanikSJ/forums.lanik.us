<?php
/**
 *
 * @package Log Connections
 * @copyright (c) 2017 david63
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

/**
 * DO NOT CHANGE
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

/**
 * DEVELOPERS PLEASE NOTE
 *
 * All language files should use UTF-8 as their encoding and the files must not contain a BOM.
 *
 * Placeholders can now contain order information, e.g. instead of
 * 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
 * translators to re-order the output of data while ensuring it remains correct
 *
 * You do not need this where single placeholders are used, e.g. 'Message %d' is fine
 * equally where a string contains only two placeholders which are used to wrap text
 * in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
 *
 * Some characters you may want to copy&paste:
 * ’ » “ ” …
 *
 */

$lang = array_merge($lang, [
	'ERROR_LOGIN_ACTIVE' 			=> '<strong>Inactive user attempted to login</strong>',
	'ERROR_LOGIN_ATTEMPTS' 			=> '<strong>User has exceeded the login attempts</strong>',
	'ERROR_LOGIN_BREAK' 			=> '<strong>The user’s login failed</strong>',
	'ERROR_LOGIN_PASSWORD' 			=> '<strong>The user entered an incorrect password</strong>',
	'ERROR_LOGIN_PASSWORD_CONVERT'	=> '<strong>Password convert error</strong>',
	'ERROR_LOGIN_UNKNOWN' 			=> '<strong>An unexpected login error (%1$s) occurred</strong><br>» %2$s',
	'ERROR_LOGIN_USERNAME' 			=> '<strong>Invalid username has been entered</strong><br>» %1$s',

	'LOG_AUTO_LOGIN' 				=> '<strong>User auto logged in</strong>',
	'LOG_AUTO_LOGIN_BROWSER' 		=> '<strong>User auto logged in</strong><br>» %1$s',
	'LOG_CONNECTIONS' 				=> 'Log connections',
	'LOG_CONNECTIONS_LOG' 			=> '<strong>Log connections settings updated</strong>',
	'LOG_NEW_USER_CREATED' 			=> '<strong>New user created</strong><br>» %1$s<br>» %2$s',
	'LOG_SUCCESSFUL' 				=> '<strong>User successfully logged in</strong>',
	'LOG_SUCCESSFUL_BROWSER' 		=> '<strong>User successfully logged in</strong><br>» %1$s',
	'LOG_USER_LOGOUT' 				=> '<strong>User logged out</strong>',

	'MANAGE_DEFAULTS' 				=> 'Manage settings',

	'NO_PASSWORD_ENTERED'			=> '<strong>No password entered</strong>',

	'UKNOWN_STATUS_ERROR' 			=> '<strong>An unknown login error occured</strong><br>» %1$s',
]);
