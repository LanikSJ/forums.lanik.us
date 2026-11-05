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
use esp\guestpseudonyms\service\viewer;

/**
 * Keep real names in outgoing e-mails and notifications.
 *
 * An e-mail is addressed to a specific, known recipient (a member) or to the
 * board admins, so it should always show real usernames — but phpBB frequently
 * builds those messages in an anonymous context (a guest registering, cron, the
 * mail queue), where the viewer would otherwise be disguised. phpBB assembles a
 * notification's e-mail body — including the actor's name, resolved through
 * user_loader::get_username() / get_username_string() — in the notify loop at
 * the end of notification_manager::add_notifications_for_users(), immediately
 * after the ..._modify_data event. So we suppress disguising from that point,
 * and lift it again at the next page_header (normal page rendering), which for
 * an action-then-confirmation request (registration, posting) runs after the
 * message has already been built.
 */
class message_listener implements EventSubscriberInterface
{
	/** @var viewer */
	protected $viewer;

	/**
	 * @param viewer $viewer
	 */
	public function __construct(viewer $viewer)
	{
		$this->viewer = $viewer;
	}

	/**
	 * @return array
	 */
	public static function getSubscribedEvents()
	{
		return [
			'core.notification_manager_add_notifications_for_users_modify_data' => 'begin_message',
			'core.page_header' => 'end_message',
		];
	}

	/**
	 * @param \phpbb\event\data $event
	 */
	public function begin_message($event)
	{
		$this->viewer->set_building_message(true);
	}

	/**
	 * @param \phpbb\event\data $event
	 */
	public function end_message($event)
	{
		$this->viewer->set_building_message(false);
	}
}
