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
 * Story C2 — swap real usernames for bogus ones in get_username_string().
 *
 * This is phpBB's central username-rendering choke point, so a single listener
 * covers post authors, topic/forum last-poster, memberlist, viewprofile, the
 * online list, the Twig username() function and user_loader::get_username().
 */
class username_listener implements EventSubscriberInterface
{
	/** @var viewer */
	protected $viewer;

	/** @var pseudonym_manager */
	protected $manager;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/**
	 * @param viewer            $viewer
	 * @param pseudonym_manager $manager
	 * @param \phpbb\user       $user
	 * @param \phpbb\auth\auth  $auth
	 */
	public function __construct(viewer $viewer, pseudonym_manager $manager, \phpbb\user $user, \phpbb\auth\auth $auth)
	{
		$this->viewer = $viewer;
		$this->manager = $manager;
		$this->user = $user;
		$this->auth = $auth;
	}

	/**
	 * @return array
	 */
	public static function getSubscribedEvents()
	{
		return [
			'core.modify_username_string' => 'disguise_username',
		];
	}

	/**
	 * @param \phpbb\event\data $event
	 */
	public function disguise_username($event)
	{
		if (!$this->viewer->is_disguised())
		{
			return;
		}

		$user_id = (int) $event['user_id'];

		// Guests/anonymous posters have no real identity to protect here; their
		// guest name is handled separately.
		if ($user_id <= 0 || $user_id === ANONYMOUS)
		{
			return;
		}

		$original = (string) $event['username'];
		if ($original === '')
		{
			return;
		}

		$bogus = $this->manager->get_pseudonym($user_id);
		if ($bogus === '' || $bogus === $original)
		{
			return;
		}

		// The event fires after username_string is built, so the generated
		// string has to be rebuilt too. Rebuild it from the mode, the way
		// get_username_string() itself does, rather than replacing the name
		// everywhere it occurs: a member called "user" or "list" would
		// otherwise have their class="username" attribute or their memberlist
		// href rewritten along with the name.
		$event['username'] = $bogus;
		$event['username_string'] = $this->rebuild_string($event, $bogus);
	}

	/**
	 * Rebuild get_username_string()'s output around the bogus name.
	 *
	 * Mirrors the templates in the function's own $_profile_cache, which the
	 * event hands us for exactly this purpose. The 'colour' and 'profile' modes
	 * return a colour and a URL with no name in them, so they are returned
	 * untouched.
	 *
	 * @param \phpbb\event\data $event
	 * @param string            $bogus
	 * @return string
	 */
	protected function rebuild_string($event, $bogus)
	{
		$mode = (string) $event['mode'];

		if ($mode === 'colour' || $mode === 'profile')
		{
			return (string) $event['username_string'];
		}

		if ($mode === 'username')
		{
			return $bogus;
		}

		if ($mode !== 'full' && $mode !== 'no_profile')
		{
			return (string) $event['username_string'];
		}

		$cache = $event['_profile_cache'];
		$user_id = (int) $event['user_id'];
		// Already '#AA0000' by the time the event fires: get_username_string()
		// prefixes the hash before dispatching. Prefixing it again produced
		// 'color: ##AA0000', which browsers drop, so every coloured name
		// (admins, moderators, coloured groups) rendered plain for guests.
		$colour = (string) $event['username_colour'];

		$profile_url = '';
		if ($mode === 'full' && $user_id && $user_id != ANONYMOUS
			&& ((int) $this->user->data['user_id'] === ANONYMOUS || $this->auth->acl_get('u_viewprofile')))
		{
			$custom_profile_url = $event['custom_profile_url'];
			$profile_url = ($custom_profile_url !== false)
				? $custom_profile_url . '&amp;u=' . $user_id
				: str_replace(['={USER_ID}', '=%7BUSER_ID%7D'], '=' . $user_id, $cache['base_url']);
		}

		if ($profile_url === '')
		{
			$template = (!$colour) ? $cache['tpl_noprofile'] : $cache['tpl_noprofile_colour'];

			return str_replace(['{USERNAME_COLOUR}', '{USERNAME}'], [$colour, $bogus], $template);
		}

		$template = (!$colour) ? $cache['tpl_profile'] : $cache['tpl_profile_colour'];

		return str_replace(['{PROFILE_URL}', '{USERNAME_COLOUR}', '{USERNAME}'], [$profile_url, $colour, $bogus], $template);
	}
}
