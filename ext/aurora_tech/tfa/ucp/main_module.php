<?php
/**
 * Aurora Tech TFA — TOTP Two Factor Authentication extension for phpBB.
 *
 * @copyright (c) 2026 Aurora Tech Inc.
 * @license GPL-2.0-only
 */

namespace aurora_tech\tfa\ucp;

/**
 * UCP module: Two Factor Authentication management.
 *
 * Screens: overview (status), activation (QR + first-code confirmation),
 * one-time backup-code display, deactivation (password confirmation),
 * backup-code regeneration (TOTP confirmation).
 *
 * phpBB p_master modules receive no dependency injection; services are
 * fetched from the global container, phpBB-style. Business logic lives in
 * the unit-tested tfa_manager — this class is glue only.
 */
class main_module
{
	/** @var string Template file assigned by phpBB module framework. */
	public $tpl_name;

	/** @var string Page title language key. */
	public $page_title;

	/** @var string Module action URL provided by p_master. */
	public $u_action;

	/**
	 * Module entry point, dispatched by phpBB's p_master.
	 *
	 * @param int    $id   Module id.
	 * @param string $mode Module mode (manage).
	 *
	 * @return void
	 */
	public function main($id, $mode)
	{
		global $phpbb_container;

		/** @var \phpbb\user $user */
		$user = $phpbb_container->get('user');
		/** @var \phpbb\request\request_interface $request */
		$request = $phpbb_container->get('request');
		/** @var \phpbb\template\template $template */
		$template = $phpbb_container->get('template');
		/** @var \phpbb\language\language $language */
		$language = $phpbb_container->get('language');
		/** @var \aurora_tech\tfa\service\tfa_manager $manager */
		$manager = $phpbb_container->get('aurora_tech.tfa.manager');
		/** @var \phpbb\log\log_interface $log */
		$log = $phpbb_container->get('log');

		$language->add_lang('tfa', 'aurora_tech/tfa');

		$this->tpl_name = 'ucp_aurora_tfa';
		$this->page_title = 'UCP_AURORA_TFA_TITLE';

		add_form_key('aurora_tfa_ucp');

		$user_id = (int) $user->data['user_id'];
		$username = (string) $user->data['username'];
		$now = time();
		$action = $request->variable('action', '');
		$error = '';

		// All state-changing actions require a valid form key.
		if ($request->is_set_post('submit') && !check_form_key('aurora_tfa_ucp'))
		{
			trigger_error($language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
		}

		switch ($action)
		{
			case 'enable':
				if ($manager->is_enabled($user_id))
				{
					break;
				}

				$this->render_enable($template, $manager->start_enrolment($username, $now));
				return;

			case 'enable_confirm':
				if (!$request->is_set_post('submit'))
				{
					break;
				}

				$pending_payload = $request->variable('pending_payload', '');
				$code = $request->variable('tfa_code', '');

				$backup_codes = $manager->complete_enrolment($user_id, $pending_payload, $code, $now);

				if ($backup_codes !== null)
				{
					$log->add('user', $user_id, $user->ip, 'LOG_AURORA_TFA_ENABLED', $now, ['reportee_id' => $user_id]);
					$this->render_codes($template, $backup_codes, 'UCP_AURORA_TFA_ENABLED_SUCCESS');
					return;
				}

				// Wrong first code: redisplay the SAME QR when the payload
				// is still valid, otherwise restart with a fresh secret.
				$pending = $manager->describe_pending($pending_payload, $username, $now);

				if ($pending === null)
				{
					$pending = $manager->start_enrolment($username, $now);
					$error = $language->lang('UCP_AURORA_TFA_PENDING_EXPIRED');
				}
				else
				{
					$error = $language->lang('UCP_AURORA_TFA_BAD_FIRST_CODE');
				}

				$this->render_enable($template, $pending, $error);
				return;

			case 'disable':
				if (!$request->is_set_post('submit') || !$manager->is_enabled($user_id))
				{
					break;
				}

				$password = $request->untrimmed_variable('password', '', true);

				/** @var \phpbb\passwords\manager $passwords_manager */
				$passwords_manager = $phpbb_container->get('passwords.manager');

				if ($password === '' || !$passwords_manager->check($password, $user->data['user_password']))
				{
					$error = $language->lang('UCP_AURORA_TFA_BAD_PASSWORD');
					break;
				}

				$manager->disable($user_id);
				$log->add('user', $user_id, $user->ip, 'LOG_AURORA_TFA_DISABLED', $now, ['reportee_id' => $user_id]);

				meta_refresh(3, $this->u_action);
				trigger_error($language->lang('UCP_AURORA_TFA_DISABLED_SUCCESS') . '<br /><br />' . $language->lang('RETURN_UCP', '<a href="' . $this->u_action . '">', '</a>'));
				break;

			case 'regenerate':
				if (!$request->is_set_post('submit') || !$manager->is_enabled($user_id))
				{
					break;
				}

				$code = $request->variable('tfa_code', '');
				$backup_codes = $manager->regenerate_backup_codes($user_id, $code, $now);

				if ($backup_codes === null)
				{
					$error = $language->lang('UCP_AURORA_TFA_BAD_CODE');
					break;
				}

				$log->add('user', $user_id, $user->ip, 'LOG_AURORA_TFA_CODES_REGENERATED', $now, ['reportee_id' => $user_id]);
				$this->render_codes($template, $backup_codes, 'UCP_AURORA_TFA_REGENERATED_SUCCESS');
				return;
		}

		// Default screen: overview.
		$status = $manager->get_status($user_id);

		$template->assign_vars([
			'S_AURORA_TFA_SCREEN'			=> 'overview',
			'S_AURORA_TFA_ENABLED'			=> $status['enabled'],
			'AURORA_TFA_ACTIVATED_AT'		=> $status['activated_at'] ? $user->format_date($status['activated_at']) : '',
			'AURORA_TFA_LAST_USED_AT'		=> $status['last_used_at'] ? $user->format_date($status['last_used_at']) : '',
			'AURORA_TFA_CODES_REMAINING'	=> $status['backup_codes_remaining'],
			'AURORA_TFA_ERROR'				=> $error,
			'U_AURORA_TFA_ACTION'			=> $this->u_action,
			'U_AURORA_TFA_ENABLE'			=> $this->u_action . '&amp;action=enable',
		]);
	}

	/**
	 * Assign template variables for the activation screen.
	 *
	 * @param \phpbb\template\template                                                              $template Template engine.
	 * @param array{secret: string, otpauth_uri: string, qr_data_uri: string, pending_payload: string} $pending  Enrolment screen data.
	 * @param string                                                                                $error    Optional error message.
	 *
	 * @return void
	 */
	protected function render_enable($template, array $pending, $error = '')
	{
		$template->assign_vars([
			'S_AURORA_TFA_SCREEN'		=> 'enable',
			'AURORA_TFA_QR_DATA_URI'	=> $pending['qr_data_uri'],
			'AURORA_TFA_SECRET'			=> $pending['secret'],
			'AURORA_TFA_OTPAUTH_URI'	=> $pending['otpauth_uri'],
			'AURORA_TFA_PENDING'		=> $pending['pending_payload'],
			'AURORA_TFA_ERROR'			=> $error,
			'U_AURORA_TFA_ACTION'		=> $this->u_action,
		]);
	}

	/**
	 * Assign template variables for the one-time backup-code display.
	 *
	 * @param \phpbb\template\template $template     Template engine.
	 * @param string[]                 $backup_codes Plaintext codes (displayed once).
	 * @param string                   $success_key  Language key of the banner message.
	 *
	 * @return void
	 */
	protected function render_codes($template, array $backup_codes, $success_key)
	{
		foreach ($backup_codes as $code)
		{
			$template->assign_block_vars('backup_codes', ['CODE' => $code]);
		}

		$template->assign_vars([
			'S_AURORA_TFA_SCREEN'		=> 'codes',
			'AURORA_TFA_SUCCESS_KEY'	=> $success_key,
			'U_AURORA_TFA_ACTION'		=> $this->u_action,
		]);
	}
}
