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
use esp\guestpseudonyms\service\pseudonym_manager;

/**
 * 1.3.0 — hide members' signatures from non-logged-in visitors (guests and
 * bots), unless the member has chosen level 3 (show real name AND signature).
 *
 * A signature frequently carries a real name, a personal site or contact
 * details, so leaving it visible would walk straight around the username
 * pseudonymisation. Logged-in members always see each other's signatures as
 * normal — only the disguised (guest/bot) view is affected.
 */
class signature_listener implements EventSubscriberInterface
{
	/** @var viewer */
	protected $viewer;

	/** @var pseudonym_manager */
	protected $manager;

	public function __construct(viewer $viewer, pseudonym_manager $manager)
	{
		$this->viewer = $viewer;
		$this->manager = $manager;
	}

	public static function getSubscribedEvents()
	{
		return [
			'core.viewtopic_modify_post_row'                     => 'hide_post_signature',
			'core.memberlist_modify_view_profile_template_vars'  => 'hide_profile_signature',
		];
	}

	/**
	 * Blank the signature under a post when the viewer is disguised and the post
	 * author has not chosen to show it.
	 *
	 * @param \phpbb\event\data $event
	 */
	public function hide_post_signature($event)
	{
		if (!$this->viewer->is_disguised())
		{
			return;
		}

		$poster_id = (int) $event['poster_id'];
		if ($poster_id <= 0 || $this->manager->shows_signature($poster_id))
		{
			return;
		}

		$post_row = $event['post_row'];
		$post_row['SIGNATURE'] = '';
		$event['post_row'] = $post_row;
	}

	/**
	 * Blank the signature on a member's profile under the same conditions.
	 *
	 * @param \phpbb\event\data $event
	 */
	public function hide_profile_signature($event)
	{
		if (!$this->viewer->is_disguised())
		{
			return;
		}

		$user_id = (int) $event['user_id'];
		if ($user_id <= 0 || $this->manager->shows_signature($user_id))
		{
			return;
		}

		$template_ary = $event['template_ary'];
		$template_ary['SIGNATURE'] = '';
		$event['template_ary'] = $template_ary;
	}
}
