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

	'WEBAUTHN_LOGIN_BUTTON'		=> 'Log in with passkey',
	'WEBAUTHN_LOGIN_SUBTEXT'	=> 'Face ID, fingerprint or security key',
	'WEBAUTHN_BTN_PREPARING'	=> 'Preparing…',
	'WEBAUTHN_BTN_WORKING'		=> 'Opening passkey…',
	'WEBAUTHN_OR'				=> 'or',
	'WEBAUTHN_ARIA_REGION'		=> 'Passkey login',
	'WEBAUTHN_REMEMBER_ME'		=> 'Remember me',

	'WEBAUTHN_REGISTER_BUTTON'	=> 'Register passkey',
	'WEBAUTHN_ADD_KEY'			=> 'Add passkey',
	'WEBAUTHN_KEY_DEFAULT_NAME'	=> 'My passkey',
	'WEBAUTHN_KEY_NAME'			=> 'Key name',
	'WEBAUTHN_KEY_CREATED'		=> 'Created',
	'WEBAUTHN_KEY_LAST_USED'	=> 'Last used',
	'WEBAUTHN_DELETE_KEY'		=> 'Delete',
	'WEBAUTHN_RENAME_KEY'		=> 'Rename',
	'WEBAUTHN_RENAME_SAVE'		=> 'Save',
	'WEBAUTHN_NO_KEYS'			=> 'You have no registered passkeys yet.',
	'WEBAUTHN_KEY_COUNT'		=> '%1$d of %2$d passkeys used',

	'WEBAUTHN_CONFIRM_DELETE_TITLE'	=> 'Delete passkey “%1$s”?',

	'WEBAUTHN_RETURN_PAGE'			=> 'Click %1$shere%2$s if your browser does not redirect you automatically.',

	'WEBAUTHN_ERROR_NOT_SUPPORTED'	=> 'Your browser does not support passkeys. Please update your browser or use your password.',
	'WEBAUTHN_ERROR_CANCELLED'		=> 'Authentication was cancelled.',
	'WEBAUTHN_ERROR_CANCELLED_OR_NONE'	=> 'No passkey found, or the request was cancelled. If you don\'t have one yet, log in with your password first, then register one in your profile.',
	'WEBAUTHN_ERROR_FAILED'			=> 'Passkey authentication failed. Please try again or log in with your password.',
	'WEBAUTHN_ERROR_REGISTER_FAILED'	=> 'Passkey registration failed. Please try again.',
	'WEBAUTHN_ERROR_ALREADY_REGISTERED'	=> 'This device or password manager already holds a passkey for your account. To add another passkey, use a different device or security key.',

	'WEBAUTHN_ERROR_DISABLED'			=> 'Passkey login is currently disabled by the board administrator.',
	'WEBAUTHN_ERROR_MAX_KEYS_REACHED'	=> 'You have reached the maximum number of passkeys (%1$d) for your account. Delete an existing passkey before adding a new one.',

	'WEBAUTHN_ERROR_CSRF'			=> 'Your session has expired or the request could not be verified. Please reload the page and try again.',

	'WEBAUTHN_ERROR_MISSING_FIELD'			=> 'The passkey response was incomplete. Please try again.',
	'WEBAUTHN_ERROR_INVALID_TYPE'			=> 'Unexpected response received. Please try again.',
	'WEBAUTHN_ERROR_INVALID_CLIENT_DATA'	=> 'The passkey response could not be read. Please try again.',
	'WEBAUTHN_ERROR_INVALID_CHALLENGE'		=> 'This passkey request has expired. Please try again.',
	'WEBAUTHN_ERROR_INVALID_ORIGIN'		=> 'This passkey could not be verified for this website address. Please try again, and contact the board administrator if this keeps happening.',
	'WEBAUTHN_ERROR_MISSING_AUTH_DATA'		=> 'The passkey response was incomplete. Please try again.',
	'WEBAUTHN_ERROR_INVALID_RP_ID'			=> 'This passkey does not belong to this website.',
	'WEBAUTHN_ERROR_UP_NOT_SET'			=> 'User presence could not be confirmed. Please try again.',
	'WEBAUTHN_ERROR_NO_CREDENTIAL_DATA'	=> 'No passkey data was received. Please try again.',
	'WEBAUTHN_ERROR_CREDENTIAL_NOT_FOUND'	=> 'This passkey is not registered on this account.',
	'WEBAUTHN_ERROR_INVALID_PUBLIC_KEY'	=> 'This passkey could not be verified. Please try again or use a different passkey.',
	'WEBAUTHN_ERROR_SIGNATURE_INVALID'		=> 'Passkey verification failed. Please try again.',
	'WEBAUTHN_ERROR_SIGN_COUNT_REPLAY'		=> 'This passkey could not be verified for security reasons. Please try again or register a new passkey.',
	'WEBAUTHN_ERROR_AUTH_DATA_TOO_SHORT'	=> 'The passkey response was invalid. Please try again.',
	'WEBAUTHN_ERROR_UNSUPPORTED_KEY_TYPE'	=> 'This type of passkey is not supported.',
	'WEBAUTHN_ERROR_INVALID_EC_KEY'		=> 'This passkey could not be registered. Please try again.',
	'WEBAUTHN_ERROR_INVALID_RSA_KEY'		=> 'This passkey could not be registered. Please try again.',
	'WEBAUTHN_ERROR_ACCOUNT_INACTIVE'		=> 'Your account is not activated, or has been deactivated by an administrator.',
	'WEBAUTHN_ERROR_UV_REQUIRED'			=> 'This passkey does not support the verification method (e.g. fingerprint or PIN) required by this board. Please use a different passkey.',

	'WEBAUTHN_SUCCESS_REGISTERED'	=> 'Passkey registered successfully.',
	'WEBAUTHN_SUCCESS_DELETED'		=> 'Passkey deleted.',
]);
