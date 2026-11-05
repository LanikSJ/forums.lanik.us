<?php
/**
 *
 * Guest pseudonyms. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Emma Laurijssens van Engelenhoven
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace esp\guestpseudonyms\ucp;

/**
 * UCP "Guest privacy" page (Story #23): shows the member their guest nickname,
 * lets them generate a new one (in their own language), and opt out of being
 * pseudonymised. Each control is gated by its ACP setting.
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
		global $request, $template, $user, $language, $phpbb_container;

		$manager = $phpbb_container->get('esp.guestpseudonyms.pseudonym_manager');
		$config = $phpbb_container->get('config');
		$language->add_lang('common', 'esp/guestpseudonyms');

		$user_id = (int) $user->data['user_id'];
		$allow_regenerate = !empty($config['guestpseudonyms_allow_regenerate']);
		$allow_opt_out = !empty($config['guestpseudonyms_allow_opt_out']);

		add_form_key('guestpseudonyms');

		$message = '';

		if ($request->is_set_post('regenerate') || $request->is_set_post('submit'))
		{
			if (!check_form_key('guestpseudonyms'))
			{
				trigger_error('FORM_INVALID');
			}

			if ($request->is_set_post('regenerate') && $allow_regenerate)
			{
				$manager->regenerate($user_id, $language->get_used_language());
				$message = 'GUESTPSEUDONYMS_REGENERATED';
			}
			else if ($request->is_set_post('submit'))
			{
				if ($allow_opt_out)
				{
					// 1 = hide name + signature, 2 = show name / hide signature,
					// 3 = show name + signature.
					$level = $request->variable('gp_privacy_level', 1);
					$manager->set_choice($user_id, $level >= 2, $level >= 3);
				}
				$message = 'GUESTPSEUDONYMS_SAVED';
			}

			meta_refresh(3, $this->u_action);
			trigger_error($language->lang($message) . '<br /><br />'
				. $language->lang('RETURN_UCP', '<a href="' . $this->u_action . '">', '</a>'));
		}

		// Arriving here from the reminder banner counts as acknowledging it
		// (#24). The confirmation timestamp is stored state, so it is only
		// re-stamped for a link this board generated — a plain page view
		// another site could trigger must not postpone the member's reminder.
		$hash = $request->variable('hash', '');
		if ($hash !== '' && check_link_hash($hash, 'guestpseudonyms_ack') && $manager->is_opted_out($user_id))
		{
			$manager->touch_opt_out($user_id);
		}

		// Current three-way choice: 1 = hide name + signature (default),
		// 2 = show name / hide signature, 3 = show name + signature.
		$level = $manager->is_opted_out($user_id) ? ($manager->shows_signature($user_id) ? 3 : 2) : 1;

		$template->assign_vars([
			'GUESTPSEUDONYMS_NICKNAME' => $manager->get_bogus($user_id),
			'S_ALLOW_REGENERATE'       => $allow_regenerate,
			'S_ALLOW_OPT_OUT'          => $allow_opt_out,
			'GUESTPSEUDONYMS_LEVEL'    => $level,
			'S_UCP_ACTION'             => $this->u_action,
		]);

		$this->tpl_name = 'ucp_guestprivacy';
		$this->page_title = $language->lang('UCP_GUESTPRIVACY');
	}
}
