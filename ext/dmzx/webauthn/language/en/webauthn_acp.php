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

	'ACP_WEBAUTHN_SETTINGS_EXPLAIN' => 'Here you are able to define passkey login, registration and session related settings.',
	'ACP_WEBAUTHN_SETTING_SAVED'    => 'Passkey settings have been saved.',

	'ACP_WEBAUTHN_GENERAL' => 'General',

	'ACP_WEBAUTHN_ENABLED'         => 'Enable passkey login',
	'ACP_WEBAUTHN_ENABLED_EXPLAIN' => 'Determines whether passkey login and passkey registration are available on this board. When disabled, the passkey login button is hidden on the login page and the registration/login endpoints refuse all requests. Users can still view, rename and delete their existing passkeys while this is disabled.',

	'ACP_WEBAUTHN_REMEMBER_ME_MODE'         => 'Remember me for passkey logins',
	'ACP_WEBAUTHN_REMEMBER_ME_MODE_EXPLAIN' => 'Determines whether a passkey login can create a long-lived "Remember me" session instead of using the standard session length.',
	'ACP_WEBAUTHN_REMEMBER_ME_OFF'          => 'Off — always use the standard session length',
	'ACP_WEBAUTHN_REMEMBER_ME_CHOICE'       => 'Let the user choose, same as password login',
	'ACP_WEBAUTHN_REMEMBER_ME_ALWAYS'       => 'Always remember passkey logins',
	'ACP_WEBAUTHN_REMEMBER_ME_BLOCKED'      => '"Allow \'Remember Me\' logins" is currently set to No under General → Security settings. This option has no effect until that is set to Yes.',

	'ACP_WEBAUTHN_USER_VERIFICATION'         => 'User verification',
	'ACP_WEBAUTHN_USER_VERIFICATION_EXPLAIN' => 'Controls whether registering or using a passkey requires the user to confirm their identity with biometrics or a device PIN, or only requires the device itself to be present.',
	'ACP_WEBAUTHN_UV_PREFERRED'              => 'Preferred — verify identity when the device supports it',
	'ACP_WEBAUTHN_UV_REQUIRED'               => 'Required — always require biometrics or a PIN',
	'ACP_WEBAUTHN_UV_DISCOURAGED'            => 'Discouraged — device presence alone is enough',

	'ACP_WEBAUTHN_MAX_KEYS'         => 'Maximum passkeys per user',
	'ACP_WEBAUTHN_MAX_KEYS_EXPLAIN' => 'The maximum number of passkeys a single user may have registered at once (1 to 10). Existing passkeys beyond a lowered limit are not removed automatically.',
]);
