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
use esp\guestpseudonyms\service\viewer;

/**
 * Stop board search from being a real-name oracle.
 *
 * search.php resolves ?author= against username_clean directly — exactly, or
 * by prefix when the term contains a wildcard — and guests hold u_search on a
 * default board. The posts come back correctly disguised, but the *matching*
 * is done on the real name, so a guest could confirm that a username exists,
 * enumerate usernames a few letters at a time, and read off each member's
 * pseudonym together with their whole post history.
 *
 * For disguised viewers the author is resolved here instead, from the name
 * they can actually see, and handed to core as an id. Core then takes its
 * author_id branch and never runs the username_clean lookup at all.
 *
 * Two deliberate consequences for disguised viewers:
 *
 *   - Wildcards no longer match. Prefix enumeration is the leak, and a single
 *     author_id cannot carry a set of matches, so a wildcard is treated as
 *     "not a displayed name" and finds nothing.
 *   - Searching for a *guest* poster by the name they typed stops working.
 *     Core supports that through a post_username match built in the same
 *     branch we are bypassing, and that constraint is not exposed to this
 *     event. Guest post names are not pseudonymised by this extension, so
 *     nothing leaks either way; the search simply no longer finds them.
 */
class search_listener implements EventSubscriberInterface
{
	/**
	 * Author id used when the requested name is not one this viewer can see.
	 *
	 * It has to be non-zero: core treats a falsy author_id as "no author given"
	 * and, with the author string blanked too, would drop the author condition
	 * entirely and search everything. A negative id is truthy and matches no
	 * post, so the search returns nothing, which is the honest answer.
	 */
	const NO_MATCH = -1;

	/** @var viewer */
	protected $viewer;

	/** @var pseudonym_manager */
	protected $manager;

	/**
	 * @param viewer            $viewer
	 * @param pseudonym_manager $manager
	 */
	public function __construct(viewer $viewer, pseudonym_manager $manager)
	{
		$this->viewer = $viewer;
		$this->manager = $manager;
	}

	/**
	 * @return array
	 */
	public static function getSubscribedEvents()
	{
		return [
			'core.search_modify_submit_parameters' => 'resolve_author_by_display_name',
		];
	}

	/**
	 * @param \phpbb\event\data $event
	 */
	public function resolve_author_by_display_name($event)
	{
		if (!$this->viewer->is_disguised())
		{
			return;
		}

		$author = trim((string) $event['author']);

		// An explicit author_id is already id-based and leaks no name.
		if ($author === '' || (int) $event['author_id'])
		{
			return;
		}

		$user_id = (strpos($author, '*') !== false)
			? 0
			: $this->manager->get_user_id_by_display_name($author);

		$event['author_id'] = $user_id > 0 ? $user_id : self::NO_MATCH;
		$event['author'] = '';
	}
}
