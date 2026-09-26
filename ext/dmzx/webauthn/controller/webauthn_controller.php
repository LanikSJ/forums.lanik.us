<?php
/**
 *
 * WebAuthn. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 dmzx, https://www.dmzx-web.net
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace dmzx\webauthn\controller;

use phpbb\config\config;
use phpbb\db\driver\driver_interface;
use phpbb\request\request;
use phpbb\request\request_interface;
use phpbb\user;
use phpbb\language\language;
use dmzx\webauthn\includes\webauthn_helper;
use Symfony\Component\HttpFoundation\JsonResponse;

class webauthn_controller
{
	private const FORM_NAME = 'dmzx_webauthn';

	private const USER_VERIFICATION_LEVELS = ['preferred', 'required', 'discouraged'];
	private const REMEMBER_ME_MODES = ['off', 'choice', 'always'];

	private const KNOWN_ERROR_KEYS = [
		'WEBAUTHN_ERROR_MISSING_FIELD',
		'WEBAUTHN_ERROR_INVALID_TYPE',
		'WEBAUTHN_ERROR_INVALID_CLIENT_DATA',
		'WEBAUTHN_ERROR_INVALID_CHALLENGE',
		'WEBAUTHN_ERROR_INVALID_ORIGIN',
		'WEBAUTHN_ERROR_MISSING_AUTH_DATA',
		'WEBAUTHN_ERROR_INVALID_RP_ID',
		'WEBAUTHN_ERROR_UP_NOT_SET',
		'WEBAUTHN_ERROR_NO_CREDENTIAL_DATA',
		'WEBAUTHN_ERROR_CREDENTIAL_NOT_FOUND',
		'WEBAUTHN_ERROR_INVALID_PUBLIC_KEY',
		'WEBAUTHN_ERROR_SIGNATURE_INVALID',
		'WEBAUTHN_ERROR_SIGN_COUNT_REPLAY',
		'WEBAUTHN_ERROR_AUTH_DATA_TOO_SHORT',
		'WEBAUTHN_ERROR_UNSUPPORTED_KEY_TYPE',
		'WEBAUTHN_ERROR_INVALID_EC_KEY',
		'WEBAUTHN_ERROR_INVALID_RSA_KEY',
		'WEBAUTHN_ERROR_ACCOUNT_INACTIVE',
		'WEBAUTHN_ERROR_UV_REQUIRED',
	];

	public function __construct(
		protected config $config,
		protected driver_interface $db,
		protected request $request,
		protected user $user,
		protected language $language,
		protected webauthn_helper $webauthn,
		protected string $root_path,
		protected string $php_ext
	) {}

	// =========================================================================
	// Registration endpoints (logged-in user registers a new passkey)
	// =========================================================================

	public function register_challenge(): JsonResponse
	{
		if (!$this->user->data['is_registered'])
		{
			return new JsonResponse(['error' => $this->language->lang('LOGIN_REQUIRED')], 403);
		}

		$this->language->add_lang('webauthn', 'dmzx/webauthn');

		if (!$this->config['dmzx_webauthn_enabled'])
		{
			return new JsonResponse(['error' => $this->language->lang('WEBAUTHN_ERROR_DISABLED')]);
		}

		$json = json_decode($this->raw_body(), true);
		if (!is_array($json) || !$this->validate_csrf($json))
		{
			return new JsonResponse(['error' => $this->csrf_error_message()], 403);
		}

		$max_keys = (int) $this->config['dmzx_webauthn_max_keys'];
		if ($this->webauthn->count_user_credentials((int) $this->user->data['user_id']) >= $max_keys)
		{
			return new JsonResponse(['error' => $this->language->lang('WEBAUTHN_ERROR_MAX_KEYS_REACHED', $max_keys)]);
		}

		$challenge = $this->webauthn->generate_challenge();
		$this->webauthn->store_challenge($challenge, (int) $this->user->data['user_id'], 'register', $this->user->data['session_id']);

		$existing            = $this->webauthn->get_user_credentials((int) $this->user->data['user_id']);
		$exclude_credentials = array_map(
			fn($c) => ['type' => 'public-key', 'id' => $c['credential_id_b64']],
			$existing
		);

		return new JsonResponse([
			'challenge'              => $challenge,
			'rp'                     => [
				'id'   => $this->webauthn->get_rp_id(),
				'name' => $this->config['sitename'],
			],
			'user'                   => [
				'id'          => $this->webauthn->base64url_encode(pack('N', $this->user->data['user_id'])),
				'name'        => $this->user->data['username'],
				'displayName' => $this->user->data['username'],
			],
			'pubKeyCredParams'        => [
				['type' => 'public-key', 'alg' => -7],
				['type' => 'public-key', 'alg' => -257],
			],
			'timeout'                => 60000,
			'excludeCredentials'     => $exclude_credentials,
			'authenticatorSelection' => [
				'userVerification' => $this->get_user_verification(),
				'residentKey'      => 'preferred',
			],
			'attestation'            => 'none',
		]);
	}

	public function register_verify(): JsonResponse
	{
		if (!$this->user->data['is_registered'])
		{
			return new JsonResponse(['error' => $this->language->lang('LOGIN_REQUIRED')], 403);
		}

		$this->language->add_lang('webauthn', 'dmzx/webauthn');

		if (!$this->config['dmzx_webauthn_enabled'])
		{
			return new JsonResponse(['error' => $this->language->lang('WEBAUTHN_ERROR_DISABLED')], 403);
		}

		$json = json_decode($this->raw_body(), true);
		if (!$json)
		{
			return new JsonResponse(['error' => $this->language->lang('WEBAUTHN_ERROR_INVALID_CLIENT_DATA')], 400);
		}

		if (!$this->validate_csrf($json))
		{
			return new JsonResponse(['error' => $this->csrf_error_message()], 403);
		}

		$max_keys = (int) $this->config['dmzx_webauthn_max_keys'];
		if ($this->webauthn->count_user_credentials((int) $this->user->data['user_id']) >= $max_keys)
		{
			return new JsonResponse(['error' => $this->language->lang('WEBAUTHN_ERROR_MAX_KEYS_REACHED', $max_keys)], 400);
		}

		try
		{
			$result = $this->webauthn->verify_registration(
				$json,
				(int) $this->user->data['user_id'],
				$this->user->data['session_id'],
				$this->get_user_verification()
			);
		}
		catch (\Exception $e)
		{
			return new JsonResponse(['error' => $this->translate_error($e->getMessage())], 400);
		}

		$name = $this->webauthn->normalise_name((string) ($json['name'] ?? ''));
		if ($name === '')
		{
			$name = $this->language->lang('WEBAUTHN_KEY_DEFAULT_NAME');
		}

		$this->webauthn->store_credential(
			$result['credential_id'],
			(int) $this->user->data['user_id'],
			$result['public_key'],
			$result['sign_count'],
			$result['aaguid'],
			$name
		);

		return new JsonResponse(['success' => true]);
	}

	// =========================================================================
	// Authentication endpoints
	// =========================================================================

	public function auth_challenge(): JsonResponse
	{
		$this->language->add_lang('webauthn', 'dmzx/webauthn');

		if (!$this->config['dmzx_webauthn_enabled'])
		{
			return new JsonResponse(['error' => $this->language->lang('WEBAUTHN_ERROR_DISABLED')]);
		}

		$challenge = $this->webauthn->generate_challenge();
		$this->webauthn->store_challenge($challenge, 0, 'authenticate', $this->user->data['session_id']);

		return new JsonResponse([
			'challenge'          => $challenge,
			'rpId'               => $this->webauthn->get_rp_id(),
			'timeout'            => 60000,
			'userVerification'   => $this->get_user_verification(),
			'allowCredentials'   => [],
		]);
	}

	public function auth_verify(): JsonResponse
	{
		$this->language->add_lang('webauthn', 'dmzx/webauthn');

		if (!$this->config['dmzx_webauthn_enabled'])
		{
			return new JsonResponse(['success' => false, 'error' => $this->language->lang('WEBAUTHN_ERROR_DISABLED')], 403);
		}

		$json = json_decode($this->raw_body(), true);
		if (!$json)
		{
			return new JsonResponse(['success' => false, 'error' => $this->language->lang('WEBAUTHN_ERROR_INVALID_CLIENT_DATA')], 400);
		}

		try
		{
			$user_id = $this->webauthn->verify_authentication(
				$json,
				$this->user->data['session_id'],
				$this->get_user_verification()
			);
		}
		catch (\Exception $e)
		{
			return new JsonResponse(['success' => false, 'error' => $this->translate_error($e->getMessage())], 400);
		}

		$persist_login = $this->should_persist_login($json);
		$local_redirect = $this->local_redirect((string) ($json['redirect'] ?? ''));

		$this->user->session_create($user_id, false, $persist_login, 1);

		$redirect = reapply_sid($local_redirect);
		$redirect = redirect($redirect, true, false);

		return new JsonResponse(['success' => true, 'redirect' => $redirect]);
	}

	/**
	 * @param string $raw Redirect value posted by the login form
	 * @return string
	 */
	private function local_redirect(string $raw): string
	{
		$default = "{$this->root_path}index.{$this->php_ext}";
		$raw     = trim($raw);

		if ($raw === '' || preg_match('#[\\\\;\x00-\x1f\x7f]#', $raw) || $raw[0] === '/' || preg_match('#(^|/)\.\.(/|$)#', $raw))
		{
			return $default;
		}

		$parts = parse_url($raw);
		if ($parts === false || isset($parts['scheme']) || isset($parts['host']) || isset($parts['user']) || isset($parts['port']))
		{
			return $default;
		}

		return $raw;
	}

	private function raw_body(): string
	{
		return (string) file_get_contents('php://input');
	}

	private function validate_csrf(array $json): bool
	{
		$creation_time = $json['creation_time'] ?? '';
		$form_token    = $json['form_token']    ?? '';

		$this->request->overwrite('creation_time', $creation_time, request_interface::POST);
		$this->request->overwrite('creation_time', $creation_time, request_interface::REQUEST);
		$this->request->overwrite('form_token', $form_token, request_interface::POST);
		$this->request->overwrite('form_token', $form_token, request_interface::REQUEST);

		return check_form_key(self::FORM_NAME);
	}

	private function csrf_error_message(): string
	{
		$this->language->add_lang('webauthn', 'dmzx/webauthn');
		return $this->language->lang('WEBAUTHN_ERROR_CSRF');
	}

	private function translate_error(string $message): string
	{
		$this->language->add_lang('webauthn', 'dmzx/webauthn');

		if (in_array($message, self::KNOWN_ERROR_KEYS, true))
		{
			return $this->language->lang($message);
		}

		error_log('[WebAuthn] Untranslated error key: ' . $message);

		return $this->language->lang('WEBAUTHN_ERROR_FAILED');
	}

	// =========================================================================
	// ACP-configurable behaviour helpers
	// =========================================================================

	private function get_user_verification(): string
	{
		$value = (string) $this->config['dmzx_webauthn_user_verification'];

		return in_array($value, self::USER_VERIFICATION_LEVELS, true) ? $value : 'preferred';
	}

	private function should_persist_login(array $json): bool
	{
		if (!$this->config['allow_autologin'])
		{
			return false;
		}

		$mode = (string) $this->config['dmzx_webauthn_remember_me_mode'];
		if (!in_array($mode, self::REMEMBER_ME_MODES, true))
		{
			$mode = 'off';
		}

		return match ($mode)
		{
			'always' => true,
			'choice' => !empty($json['remember']),
			default  => false,
		};
	}
}
