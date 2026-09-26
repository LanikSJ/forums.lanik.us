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
use phpbb\user;
use phpbb\language\language;
use phpbb\template\template;
use phpbb\controller\helper as controller_helper;
use dmzx\webauthn\includes\webauthn_helper;

class ucp_controller
{
	private const FORM_NAME = 'dmzx_webauthn';

	public function __construct(
		protected config $config,
		protected driver_interface $db,
		protected request $request,
		protected user $user,
		protected language $language,
		protected template $template,
		protected controller_helper $helper,
		protected webauthn_helper $webauthn,
		protected string $root_path,
		protected string $php_ext
	) {}

	public function main(string $u_action): void
	{
		if ($this->request->variable('action', '') === 'delete')
		{
			$this->handle_delete($u_action);
		}

		if ($this->request->is_set_post('rename_credential'))
		{
			if (!check_form_key(self::FORM_NAME))
			{
				trigger_error('FORM_INVALID');
			}

			$cred_id  = $this->request->variable('credential_id', '');
			$new_name = $this->webauthn->normalise_name(
				htmlspecialchars_decode($this->request->variable('new_name', '', true), ENT_COMPAT)
			);

			if ($cred_id !== '' && $new_name !== '')
			{
				$this->webauthn->rename_credential(
					$cred_id,
					(int) $this->user->data['user_id'],
					$new_name
				);
			}
		}

		$credentials = $this->webauthn->get_user_credentials((int) $this->user->data['user_id']);

		foreach ($credentials as $cred)
		{
			$this->template->assign_block_vars('credentials', [
				'ID'        => $cred['credential_id'],
				'NAME'      => $cred['name'],
				'CREATED'   => $this->user->format_date((int) $cred['created']),
				'LAST_USED' => $this->user->format_date((int) $cred['last_used']),
			]);
		}

		$max_keys = (int) $this->config['dmzx_webauthn_max_keys'];

		add_form_key(self::FORM_NAME);

		$this->template->assign_vars([
			'U_ACTION'                    => $u_action,
			'WEBAUTHN_REG_CHALLENGE_URL'  => $this->helper->route('dmzx_webauthn_register_challenge'),
			'WEBAUTHN_REG_VERIFY_URL'     => $this->helper->route('dmzx_webauthn_register_verify'),
			'S_WEBAUTHN_ENABLED'          => (bool) $this->config['dmzx_webauthn_enabled'],
			'WEBAUTHN_KEY_COUNT'          => count($credentials),
			'WEBAUTHN_MAX_KEYS'           => $max_keys,
			'S_WEBAUTHN_MAX_KEYS_REACHED' => count($credentials) >= $max_keys,
		]);
	}

	private function handle_delete(string $return_url): void
	{
		$delete_id = $this->request->variable('credential_id', '');

		$credential_name = '';
		$found            = false;

		foreach ($this->webauthn->get_user_credentials((int) $this->user->data['user_id']) as $cred)
		{
			if ($cred['credential_id'] === $delete_id)
			{
				$credential_name = $cred['name'];
				$found           = true;
				break;
			}
		}

		if (!$found)
		{
			redirect($return_url);
		}

		if (confirm_box(true))
		{
			$this->webauthn->delete_credential($delete_id, (int) $this->user->data['user_id']);

			meta_refresh(3, $return_url);
			$message = $this->language->lang('WEBAUTHN_SUCCESS_DELETED');
			$message .= '<br /><br />' . $this->language->lang('WEBAUTHN_RETURN_PAGE', '<a href="' . $return_url . '">', '</a>');
			trigger_error($message);
		}

		if ($this->request->is_set_post('cancel'))
		{
			redirect($return_url);
		}

		confirm_box(false, $this->language->lang('WEBAUTHN_CONFIRM_DELETE_TITLE', utf8_htmlspecialchars($credential_name)), build_hidden_fields([
			'credential_id' => $delete_id,
			'action'        => 'delete',
		]));
	}
}
