<?php
/**
 *
 * WebAuthn. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 dmzx, https://www.dmzx-web.net
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace dmzx\webauthn\event;

use phpbb\config\config;
use phpbb\user;
use phpbb\template\template;
use phpbb\request\request;
use phpbb\controller\helper as controller_helper;
use phpbb\language\language;
use dmzx\webauthn\includes\webauthn_helper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{
	public function __construct(
		protected config $config,
		protected user $user,
		protected template $template,
		protected request $request,
		protected controller_helper $helper,
		protected language $language,
		protected webauthn_helper $webauthn,
		protected string $php_ext
	) {}

	public static function getSubscribedEvents(): array
	{
		return [
			'core.user_setup'		=> 'load_language_on_setup',
			'core.page_header_after'	=> 'inject_webauthn_on_index',
			'core.login_box_before'	=> 'inject_webauthn_on_login_box',
			'core.delete_user_after'	=> 'delete_orphaned_credentials',
		];
	}

	public function load_language_on_setup($event): void
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = [
			'ext_name' => 'dmzx/webauthn',
			'lang_set' => 'webauthn',
		];
		$event['lang_set_ext'] = $lang_set_ext;
	}

	public function inject_webauthn_on_index(): void
	{
		$script = basename(
			$this->request->server('SCRIPT_NAME') ?: '',
			'.' . $this->php_ext
		);

		$on_index = !$this->user->data['is_registered']
			&& $script === 'index'
			&& (bool) $this->config['dmzx_webauthn_enabled'];

		if (!$on_index)
		{
			return;
		}

		$this->assign_webauthn_vars();
	}

	public function inject_webauthn_on_login_box($event): void
	{
		if ($event['admin'] || $this->user->data['is_registered'] || !(bool) $this->config['dmzx_webauthn_enabled'])
		{
			return;
		}

		$this->assign_webauthn_vars();
	}

	public function delete_orphaned_credentials($event): void
	{
		$this->webauthn->delete_credentials_for_users($event['user_ids']);
	}

	private function assign_webauthn_vars(): void
	{
		$this->template->assign_vars([
			'WEBAUTHN_ON_LOGIN'         => true,
			'WEBAUTHN_CHALLENGE_URL'    => $this->helper->route('dmzx_webauthn_auth_challenge'),
			'WEBAUTHN_VERIFY_URL'       => $this->helper->route('dmzx_webauthn_auth_verify'),
			'WEBAUTHN_REMEMBER_ME_MODE' => (string) $this->config['dmzx_webauthn_remember_me_mode'],
		]);
	}
}
