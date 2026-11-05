<?php
/**
 *
 * Guest pseudonyms. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Emma Laurijssens van Engelenhoven
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace esp\guestpseudonyms\acp;

/**
 * ACP settings page (Story #14).
 */
class main_module
{
	/** @var string */
	public $u_action;

	/** @var string */
	public $tpl_name;

	/** @var string */
	public $page_title;

	public function main($id, $mode)
	{
		global $request, $template, $language, $phpbb_container;

		$config = $phpbb_container->get('config');
		/** @var \phpbb\log\log_interface $phpbb_log */
		$phpbb_log = $phpbb_container->get('log');
		$language->add_lang('info_acp_guestprivacy', 'esp/guestpseudonyms');

		$this->tpl_name = 'acp_guestprivacy';
		$this->page_title = $language->lang('ACP_GUESTPRIVACY_TITLE');

		add_form_key('guestpseudonyms_acp');

		if ($request->is_set_post('submit'))
		{
			if (!check_form_key('guestpseudonyms_acp'))
			{
				trigger_error($language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			$config->set('guestpseudonyms_enable', $request->variable('gp_enable', 0));
			$config->set('guestpseudonyms_resolve_names', $request->variable('gp_resolve_names', 0));
			$config->set('guestpseudonyms_allow_regenerate', $request->variable('gp_allow_regenerate', 0));
			$config->set('guestpseudonyms_allow_opt_out', $request->variable('gp_allow_opt_out', 0));
			$config->set('guestpseudonyms_optout_reminder_months', abs($request->variable('gp_reminder_months', 0)));
			$config->set('guestpseudonyms_optout_autorevert', $request->variable('gp_autorevert', 0));

			// Settings pages in core record who changed what; without this the
			// admin log shows no trace of a board-wide privacy setting moving.
			$phpbb_log->add('admin', $phpbb_container->get('user')->data['user_id'],
				$phpbb_container->get('user')->ip, 'LOG_GUESTPSEUDONYMS_SETTINGS');

			trigger_error($language->lang('GUESTPSEUDONYMS_ACP_SAVED') . adm_back_link($this->u_action));
		}

		$template->assign_vars([
			'U_ACTION'            => $this->u_action,
			'GP_ENABLE'           => (bool) $config['guestpseudonyms_enable'],
			'GP_RESOLVE_NAMES'    => (bool) $config['guestpseudonyms_resolve_names'],
			'GP_ALLOW_REGENERATE' => (bool) $config['guestpseudonyms_allow_regenerate'],
			'GP_ALLOW_OPT_OUT'    => (bool) $config['guestpseudonyms_allow_opt_out'],
			'GP_REMINDER_MONTHS'  => (int) $config['guestpseudonyms_optout_reminder_months'],
			'GP_AUTOREVERT'       => (bool) $config['guestpseudonyms_optout_autorevert'],
		]);
	}
}
