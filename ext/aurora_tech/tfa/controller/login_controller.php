<?php
/**
 * Aurora Tech TFA — TOTP Two Factor Authentication extension for phpBB.
 *
 * @copyright (c) 2026 Aurora Tech Inc.
 * @license GPL-2.0-only
 */

namespace aurora_tech\tfa\controller;

use aurora_tech\tfa\helper\login_page;
use aurora_tech\tfa\service\login_challenge_service;
use aurora_tech\tfa\service\tfa_manager;

/**
 * Second-factor verification controller.
 *
 * Receives the code typed on the verification page, checks it against
 * the pending challenge and — only then — creates the phpBB session the
 * core skipped. Request glue; the verification logic itself lives in the
 * unit-tested services.
 */
class login_controller
{
	protected tfa_manager $manager;

	protected login_challenge_service $challenges;

	protected login_page $login_page;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\log\log_interface */
	protected $log;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	protected string $root_path;

	protected string $php_ext;

	/**
	 * @param tfa_manager                       $manager    TFA facade.
	 * @param login_challenge_service           $challenges Challenge lifecycle.
	 * @param login_page                        $login_page Verification page renderer.
	 * @param \phpbb\user                       $user       Current (pre-login) user/session.
	 * @param \phpbb\request\request_interface  $request    Request abstraction.
	 * @param \phpbb\language\language          $language   Language service.
	 * @param \phpbb\log\log_interface          $log        phpBB log.
	 * @param \phpbb\db\driver\driver_interface $db         Database driver.
	 * @param string                            $root_path  Board root path.
	 * @param string                            $php_ext    PHP file extension.
	 */
	public function __construct(
		tfa_manager $manager,
		login_challenge_service $challenges,
		login_page $login_page,
		$user,
		$request,
		$language,
		$log,
		$db,
		$root_path,
		$php_ext
	)
	{
		$this->manager = $manager;
		$this->challenges = $challenges;
		$this->login_page = $login_page;
		$this->user = $user;
		$this->request = $request;
		$this->language = $language;
		$this->log = $log;
		$this->db = $db;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * Handle the verification form submission.
	 *
	 * @return void Never returns normally: re-renders the form, redirects
	 *              or triggers an error, all of which end the request.
	 */
	public function submit()
	{
		$this->language->add_lang('tfa', 'aurora_tech/tfa');

		// Direct navigation (browser back button, refresh, bookmarked
		// URL): no form was posted, walk back to the board index
		// gracefully instead of a method-not-allowed error.
		if (!$this->request->is_set_post('challenge_token'))
		{
			redirect(append_sid("{$this->root_path}index.{$this->php_ext}"));
		}

		$now = time();
		$session_id = (string) $this->user->session_id;
		$token = $this->request->variable('challenge_token', '');

		$challenge = $this->challenges->validate($session_id, $token, $now);

		if ($challenge === null)
		{
			// Expired or forged: back to square one, no information leaked.
			trigger_error($this->language->lang('AURORA_TFA_CHALLENGE_EXPIRED') . '<br /><br />' . $this->language->lang('RETURN_INDEX', '<a href="' . append_sid("{$this->root_path}index.{$this->php_ext}") . '">', '</a>'));
		}

		$user_id = (int) $challenge['user_id'];
		$user_lang = $this->get_user_lang($user_id);

		if (!check_form_key('aurora_tfa_login'))
		{
			$this->login_page->render_and_halt($token, $this->language->lang('FORM_INVALID'), $user_lang);
		}

		$mode = $this->request->variable('tfa_mode', 'totp');
		$code = $this->request->variable('tfa_code', '');

		$valid = ($mode === 'backup')
			? $this->manager->verify_backup_login($user_id, $code, $now)
			: $this->manager->verify_totp_login($user_id, $code, $now);

		if (!$valid)
		{
			$this->log->add('user', $user_id, $this->user->ip, 'LOG_AURORA_TFA_LOGIN_FAILED', $now, ['reportee_id' => $user_id]);
			$this->login_page->render_and_halt($token, $this->language->lang('AURORA_TFA_BAD_LOGIN_CODE'), $user_lang);
		}

		$this->challenges->consume($session_id);
		$this->create_session($challenge);
	}

	/**
	 * Look up the stored language ISO code of a user.
	 *
	 * Used to render the verification page in the authenticating user's
	 * language, since no session exists yet at that point.
	 *
	 * @param int $user_id Board user id.
	 *
	 * @return string|null ISO code (e.g. "fr", "fr_ca"), or null when unknown.
	 */
	protected function get_user_lang($user_id)
	{
		$sql = 'SELECT user_lang
			FROM ' . USERS_TABLE . '
			WHERE user_id = ' . (int) $user_id;
		$result = $this->db->sql_query($sql);
		$user_lang = $this->db->sql_fetchfield('user_lang');
		$this->db->sql_freeresult($result);

		return $user_lang !== false ? (string) $user_lang : null;
	}

	/**
	 * Create the phpBB session the core skipped, mirroring the tail end
	 * of \phpbb\auth\auth::login().
	 *
	 * @param array{user_id: int, is_admin: bool, auto_login: bool, viewonline: bool, redirect_url: string} $challenge Validated challenge.
	 *
	 * @return void Ends the request with a redirect.
	 */
	protected function create_session(array $challenge)
	{
		$old_session_id = $this->user->session_id;

		if ($challenge['is_admin'])
		{
			$cookie_expire = time() - 31536000;
			$this->user->set_cookie('u', '', $cookie_expire);
			$this->user->set_cookie('sid', '', $cookie_expire);
		}

		$result = $this->user->session_create(
			$challenge['user_id'],
			$challenge['is_admin'],
			$challenge['auto_login'],
			$challenge['viewonline']
		);

		if ($result !== true)
		{
			trigger_error($this->language->lang('AURORA_TFA_SESSION_FAILED'));
		}

		if ($challenge['is_admin'])
		{
			// Admin re-authentication: drop the pre-login session row and
			// head to the ACP, as the core does.
			$sql = 'DELETE FROM ' . SESSIONS_TABLE . "
				WHERE session_id = '" . $this->db->sql_escape($old_session_id) . "'
					AND session_user_id = " . (int) $challenge['user_id'];
			$this->db->sql_query($sql);

			redirect(append_sid("{$this->root_path}adm/index.{$this->php_ext}", false, true, $this->user->data['session_id']));
		}

		$redirect = $challenge['redirect_url'] !== '' ? $challenge['redirect_url'] : "index.{$this->php_ext}";
		$redirect = reapply_sid($this->root_path . str_replace('&amp;', '&', $redirect));

		redirect($redirect, false, true);
	}
}
