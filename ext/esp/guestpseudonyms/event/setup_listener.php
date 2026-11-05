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

/**
 * Loads the extension's language on every page so the UCP navigation title and
 * page strings are available.
 */
class setup_listener implements EventSubscriberInterface
{
	public static function getSubscribedEvents()
	{
		return [
			'core.user_setup' => 'load_language',
		];
	}

	/**
	 * @param \phpbb\event\data $event
	 */
	public function load_language($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = [
			'ext_name' => 'esp/guestpseudonyms',
			// 'common' for the UCP page; 'info_acp_guestprivacy' so the ACP
			// Extensions menu can translate this module's title.
			'lang_set' => ['common', 'info_acp_guestprivacy'],
		];
		$event['lang_set_ext'] = $lang_set_ext;
	}
}
