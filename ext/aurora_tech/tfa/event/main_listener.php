<?php
/**
 * Aurora Tech TFA — TOTP Two Factor Authentication extension for phpBB.
 *
 * @copyright (c) 2026 Aurora Tech Inc.
 * @license GPL-2.0-only
 */

namespace aurora_tech\tfa\event;

use aurora_tech\tfa\helper\login_page;
use aurora_tech\tfa\service\login_challenge_service;
use aurora_tech\tfa\service\policy_service;
use aurora_tech\tfa\service\tfa_manager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Login-flow and policy-enforcement listener.
 *
 * Login: intercepts the moment between "password accepted" and "session
 * created". For TFA-enrolled users, no session is created: a server-side
 * challenge is issued and the second-factor form is rendered instead.
 *
 * Policy: when TFA is required for the user's groups and the user has
 * not enrolled yet, every page except the enrolment screen (and the
 * login/logout flows) redirects to the enrolment screen.
 */
class main_listener implements EventSubscriberInterface
{
	protected tfa_manager $manager;

	protected login_challenge_service $challenges;

	protected login_page $login_page;

	protected policy_service $policy;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	protected string $user_group_table;

	protected string $root_path;

	protected string $php_ext;

	/**
	 * @param tfa_manager                       $manager          TFA facade.
	 * @param login_challenge_service           $challenges       Challenge lifecycle.
	 * @param login_page                        $login_page       Verification page renderer.
	 * @param policy_service                    $policy           Board-wide policy.
	 * @param \phpbb\user                       $user             Current user/session.
	 * @param \phpbb\request\request_interface  $request          Request abstraction.
	 * @param \phpbb\db\driver\driver_interface $db               Database driver.
	 * @param string                            $user_group_table Fully prefixed user_group table.
	 * @param string                            $root_path        Board root path.
	 * @param string                            $php_ext          PHP file extension.
	 */
	public function __construct(
		tfa_manager $manager,
		login_challenge_service $challenges,
		login_page $login_page,
		policy_service $policy,
		$user,
		$request,
		$db,
		string $user_group_table,
		string $root_path,
		string $php_ext
	)
	{
		$this->manager = $manager;
		$this->challenges = $challenges;
		$this->login_page = $login_page;
		$this->policy = $policy;
		$this->user = $user;
		$this->request = $request;
		$this->db = $db;
		$this->user_group_table = $user_group_table;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * Return the phpBB core events this listener subscribes to.
	 *
	 * @return array<string, string> Map of event name to handler method.
	 */
	public static function getSubscribedEvents()
	{
		return [
			'core.auth_login_session_create_before'	=> 'on_login_session_create_before',
			'core.user_setup_after'					=> 'on_user_setup_after',
		];
	}

	/**
	 * Interpose the second factor between password check and session
	 * creation.
	 *
	 * @param \phpbb\event\data $event Core event (login, admin, username, autologin).
	 *
	 * @return void Does not return when a challenge is issued: the
	 *              verification page ends the request.
	 */
	public function on_login_session_create_before($event)
	{
		if ($this->policy->is_disabled())
		{
			return;
		}

		$login = $event['login'];

		if (!is_array($login) || !isset($login['status']) || $login['status'] != LOGIN_SUCCESS)
		{
			return;
		}

		$user_id = (int) $login['user_row']['user_id'];

		if (!$this->manager->is_enabled($user_id))
		{
			return;
		}

		// Mirror the core's viewonline computation (a checked box means
		// "hide me", hence the negation).
		$viewonline = !$this->request->is_set_post('viewonline');

		$token = $this->challenges->issue(
			(string) $this->user->session_id,
			$user_id,
			(bool) $event['admin'],
			(bool) $event['autologin'],
			$viewonline,
			$this->request->variable('redirect', ''),
			time()
		);

		$user_lang = isset($login['user_row']['user_lang']) ? (string) $login['user_row']['user_lang'] : null;

		$this->login_page->render_and_halt($token, '', $user_lang);
	}

	/**
	 * Enforce mandatory enrolment: members of required groups without an
	 * enrolment are walked to the UCP enrolment screen.
	 *
	 * @param \phpbb\event\data $event Core event.
	 *
	 * @return void Does not return when a redirect is issued.
	 */
	public function on_user_setup_after($event)
	{
		if ($this->policy->get_mode() !== policy_service::MODE_REQUIRED_GROUPS)
		{
			return;
		}

		$user_id = (int) $this->user->data['user_id'];

		if ($user_id == ANONYMOUS || !empty($this->user->data['is_bot']))
		{
			return;
		}

		if ($this->is_exempt_page())
		{
			return;
		}

		// Cheap check first (single-row lookup), group scan only for
		// non-enrolled users.
		if ($this->manager->is_enabled($user_id))
		{
			return;
		}

		if (!$this->policy->is_enrolment_required_for_groups($this->get_user_group_ids($user_id)))
		{
			return;
		}

		redirect(append_sid("{$this->root_path}ucp.{$this->php_ext}", 'i=-aurora_tech-tfa-ucp-main_module&mode=manage'));
	}

	/**
	 * Pages that must stay reachable for a non-enrolled user, otherwise
	 * enrolment (or leaving) becomes impossible.
	 *
	 * @return bool True when the current page is exempt from enforcement.
	 */
	protected function is_exempt_page(): bool
	{
		$page_name = (string) ($this->user->page['page_name'] ?? '');

		if ($page_name === 'ucp.' . $this->php_ext)
		{
			$i = $this->request->variable('i', '');
			$mode = $this->request->variable('mode', '');

			// Our own UCP module, and the session-management modes.
			return strpos($i, 'aurora_tech') !== false
				|| in_array($mode, ['login', 'logout', 'confirm', 'delete_cookies'], true);
		}

		// Contact page stays reachable (locked-out users need a way to
		// reach the administrators).
		if ($page_name === 'memberlist.' . $this->php_ext)
		{
			return $this->request->variable('mode', '') === 'contactadmin';
		}

		return false;
	}

	/**
	 * All (non-pending) group ids of a user.
	 *
	 * @param int $user_id Board user id.
	 *
	 * @return int[] Group ids.
	 */
	protected function get_user_group_ids(int $user_id): array
	{
		$sql = 'SELECT group_id
			FROM ' . $this->user_group_table . '
			WHERE user_id = ' . $user_id . '
				AND user_pending = 0';
		$result = $this->db->sql_query($sql);

		$group_ids = [];

		while ($row = $this->db->sql_fetchrow($result))
		{
			$group_ids[] = (int) $row['group_id'];
		}

		$this->db->sql_freeresult($result);

		return $group_ids;
	}
}
