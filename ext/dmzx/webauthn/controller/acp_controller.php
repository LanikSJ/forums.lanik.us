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
use phpbb\request\request;
use phpbb\language\language;
use phpbb\template\template;

class acp_controller
{
	private const FORM_NAME = 'dmzx_webauthn_acp';

	private const REMEMBER_ME_MODES = ['off', 'choice', 'always'];
	private const USER_VERIFICATION_LEVELS = ['preferred', 'required', 'discouraged'];

	private const MAX_KEYS_FLOOR   = 1;
	private const MAX_KEYS_CEILING = 10;

	public function __construct(
		protected config $config,
		protected request $request,
		protected language $language,
		protected template $template
	) {}

	public function main(string $u_action): void
	{
		$this->language->add_lang('webauthn_acp', 'dmzx/webauthn');

		if ($this->request->is_set_post('submit'))
		{
			if (!check_form_key(self::FORM_NAME))
			{
				trigger_error('FORM_INVALID');
			}

			$enabled            = $this->request->variable('enabled', false);
			$remember_me_mode   = $this->request->variable('remember_me_mode', 'off');
			$user_verification  = $this->request->variable('user_verification', 'preferred');
			$max_keys           = $this->request->variable('max_keys', 5);

			if (!in_array($remember_me_mode, self::REMEMBER_ME_MODES, true))
			{
				$remember_me_mode = 'off';
			}

			if (!in_array($user_verification, self::USER_VERIFICATION_LEVELS, true))
			{
				$user_verification = 'preferred';
			}

			$max_keys = max(self::MAX_KEYS_FLOOR, min(self::MAX_KEYS_CEILING, $max_keys));

			$this->config->set('dmzx_webauthn_enabled', $enabled ? 1 : 0);
			$this->config->set('dmzx_webauthn_remember_me_mode', $remember_me_mode);
			$this->config->set('dmzx_webauthn_user_verification', $user_verification);
			$this->config->set('dmzx_webauthn_max_keys', $max_keys);

			trigger_error($this->language->lang('ACP_WEBAUTHN_SETTING_SAVED') . adm_back_link($u_action));
		}

		add_form_key(self::FORM_NAME);

		$this->template->assign_vars([
			'S_WEBAUTHN_ENABLED'         => (bool) $this->config['dmzx_webauthn_enabled'],
			'WEBAUTHN_REMEMBER_ME_MODE'  => (string) $this->config['dmzx_webauthn_remember_me_mode'],
			'WEBAUTHN_USER_VERIFICATION' => (string) $this->config['dmzx_webauthn_user_verification'],
			'WEBAUTHN_MAX_KEYS'          => (int) $this->config['dmzx_webauthn_max_keys'],
			'S_WEBAUTHN_ALLOW_AUTOLOGIN' => (bool) $this->config['allow_autologin'],
			'U_ACTION'                   => $u_action,
		]);
	}
}
