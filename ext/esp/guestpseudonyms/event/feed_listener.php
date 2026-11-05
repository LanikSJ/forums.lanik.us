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
 * Story E1 — disguise author names in ATOM/RSS feeds.
 *
 * Feeds build the author from the raw username column, bypassing
 * get_username_string(), so C2 never sees them. We rewrite the author name on
 * core.feed_modify_feed_row (before the item and its statistics are built) using
 * the feed's own author-id field, so the same pseudonym is used as everywhere
 * else. Feeds are unauthenticated, so the viewer is effectively always a guest.
 */
class feed_listener implements EventSubscriberInterface
{
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
			'core.feed_modify_feed_row' => 'disguise_feed_author',
		];
	}

	/**
	 * @param \phpbb\event\data $event
	 */
	public function disguise_feed_author($event)
	{
		if (!$this->viewer->is_disguised())
		{
			return;
		}

		$feed = $event['feed'];
		$creator_field = $feed->get('creator');
		$author_id_field = $feed->get('author_id');

		if ($creator_field === null || $author_id_field === null)
		{
			return;
		}

		$row = $event['row'];
		if (!isset($row[$author_id_field], $row[$creator_field]))
		{
			return;
		}

		$user_id = (int) $row[$author_id_field];
		if ($user_id > 0 && $user_id !== ANONYMOUS)
		{
			$row[$creator_field] = $this->manager->get_pseudonym($user_id);
			$event['row'] = $row;
		}
	}
}
