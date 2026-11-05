<?php
/**
 *
 * Guest pseudonyms. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Emma Laurijssens van Engelenhoven
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace esp\guestpseudonyms\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use esp\guestpseudonyms\service\pseudonym_manager;

/**
 * Story #24 — periodically remind opted-out members that their real name is
 * public. Once the configured interval has passed since a member last confirmed
 * their opt-out, either re-protect them (fail-closed, if auto-revert is on) or
 * show a reminder banner linking to their Guest privacy page.
 */
class reminder_listener implements EventSubscriberInterface
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var pseudonym_manager */
	protected $manager;

	/** @var string */
	protected $root_path;

	/** @var string */
	protected $php_ext;

	/** roughly one month in seconds */
	const MONTH = 2629800;

	public function __construct(\phpbb\config\config $config, \phpbb\user $user, \phpbb\template\template $template, pseudonym_manager $manager, $root_path, $php_ext)
	{
		$this->config = $config;
		$this->user = $user;
		$this->template = $template;
		$this->manager = $manager;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
	}

	public static function getSubscribedEvents()
	{
		return [
			'core.page_header_after' => 'check_reminder',
		];
	}

	public function check_reminder($event)
	{
		$months = (int) $this->config['guestpseudonyms_optout_reminder_months'];
		if ($months <= 0)
		{
			return;
		}

		// Members only.
		if ((int) $this->user->data['user_id'] === ANONYMOUS || !empty($this->user->data['is_bot']))
		{
			return;
		}

		$user_id = (int) $this->user->data['user_id'];
		if (!$this->manager->is_opted_out($user_id))
		{
			return;
		}

		if (time() < $this->manager->get_opt_out_confirmed_at($user_id) + $months * self::MONTH)
		{
			return; // not due yet
		}

		if (!empty($this->config['guestpseudonyms_optout_autorevert']))
		{
			// Fail-closed: back to full protection (pseudonym, signature hidden).
			$this->manager->set_choice($user_id, false, false);
			return;
		}

		// Remind-only: show a banner linking to the UCP page.
		$this->template->assign_vars([
			'S_GUESTPSEUDONYMS_REMINDER' => true,
			// The hash lets the UCP page treat arriving here as acknowledging
			// the reminder without a bare page view being able to do so.
			'U_GUESTPSEUDONYMS_UCP'      => append_sid("{$this->root_path}ucp.{$this->php_ext}",
				'i=-esp-guestpseudonyms-ucp-main_module&amp;mode=settings&amp;hash=' . generate_link_hash('guestpseudonyms_ack')),
		]);
	}
}
