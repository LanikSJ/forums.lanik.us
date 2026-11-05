<?php
/**
 *
 * Guest pseudonyms. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Emma Laurijssens van Engelenhoven
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace esp\guestpseudonyms\service;

/**
 * Story C1 / #14 — decides whether the current viewer should see bogus names.
 *
 * True for every non-logged-in viewer — guests (the anonymous user) and
 * search-engine bots/crawlers alike. Honours the master enable switch. Logged-in
 * members always see real usernames. (An admin "preview as guest" toggle is
 * Story #6.)
 *
 * One exception overrides everything: while phpBB is assembling an e-mail or
 * notification, names are never disguised. Such messages are addressed to a
 * specific, known recipient (a member, or the board admins), so the real name
 * belongs there — even though the sending context is frequently the anonymous
 * user (a guest registering, the mail queue, cron). The message_listener toggles
 * this flag around notification building.
 */
class viewer
{
	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var bool True while an e-mail/notification is being built. */
	protected $building_message = false;

	/**
	 * @param \phpbb\user          $user
	 * @param \phpbb\config\config $config
	 */
	public function __construct(\phpbb\user $user, \phpbb\config\config $config)
	{
		$this->user = $user;
		$this->config = $config;
	}

	/**
	 * Mark whether phpBB is currently assembling an e-mail/notification, during
	 * which names must not be disguised.
	 *
	 * @param bool $building
	 */
	public function set_building_message($building)
	{
		$this->building_message = (bool) $building;
	}

	/**
	 * @return bool True if names should be disguised for this viewer.
	 */
	public function is_disguised()
	{
		// Never disguise inside an outgoing message — it is addressed to a known
		// recipient, so it should carry real names.
		if ($this->building_message)
		{
			return false;
		}

		if (empty($this->config['guestpseudonyms_enable']))
		{
			return false;
		}

		// Every non-logged-in viewer is disguised — guests and search-engine
		// bots alike. There is deliberately no separate bot switch: the whole
		// point is keeping real names off the public, googleable surface, so a
		// member's choice must protect them from search engines too.
		return (int) $this->user->data['user_id'] === ANONYMOUS || !empty($this->user->data['is_bot']);
	}
}
