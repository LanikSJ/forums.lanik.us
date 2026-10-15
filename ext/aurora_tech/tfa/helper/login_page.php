<?php
/**
 * Aurora Tech TFA — TOTP Two Factor Authentication extension for phpBB.
 *
 * @copyright (c) 2026 Aurora Tech Inc.
 * @license GPL-2.0-only
 */

namespace aurora_tech\tfa\helper;

/**
 * Renders the second-factor verification page and halts request
 * processing (page_footer() ends the request, phpBB-style).
 *
 * Presentation glue shared by the login listener and the verification
 * controller; covered by the live test bench rather than unit tests.
 */
class login_page
{
	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\controller\helper */
	protected $controller_helper;

	/**
	 * @param \phpbb\template\template  $template          Template engine.
	 * @param \phpbb\language\language  $language          Language service.
	 * @param \phpbb\controller\helper  $controller_helper Route generator.
	 */
	public function __construct($template, $language, $controller_helper)
	{
		$this->template = $template;
		$this->language = $language;
		$this->controller_helper = $controller_helper;
	}

	/**
	 * Render the verification page and end the request.
	 *
	 * @param string      $challenge_token Token bound to the pending challenge.
	 * @param string      $error           Optional error message to display.
	 * @param string|null $user_lang       ISO code of the authenticating user's
	 *                                     language. This page renders before the
	 *                                     session exists, so without this the
	 *                                     board default language is used; forcing
	 *                                     the enrolled user's language shows the
	 *                                     verification screen in their own tongue.
	 *
	 * @return void This method does not return: page_footer() exits.
	 */
	public function render_and_halt($challenge_token, $error = '', $user_lang = null)
	{
		if ($user_lang !== null && $user_lang !== '')
		{
			// Switch to the authenticating user's language and reload the
			// already-loaded common language files in it, so the whole page
			// (not just our strings) matches their preference.
			$this->language->set_user_language($user_lang, true);
		}

		$this->language->add_lang('tfa', 'aurora_tech/tfa');

		add_form_key('aurora_tfa_login');

		$this->template->assign_vars([
			'AURORA_TFA_CHALLENGE_TOKEN'	=> $challenge_token,
			'AURORA_TFA_ERROR'				=> $error,
			'U_AURORA_TFA_SUBMIT'			=> $this->controller_helper->route('aurora_tech_tfa_login_submit'),
		]);

		page_header($this->language->lang('AURORA_TFA_VERIFY_TITLE'));

		$this->template->set_filenames([
			'body'	=> '@aurora_tech_tfa/aurora_tfa_login.html',
		]);

		page_footer(false);
	}
}
