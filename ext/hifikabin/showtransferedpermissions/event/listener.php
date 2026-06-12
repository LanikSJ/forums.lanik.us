<?php

/**
*
* @package phpBB Extension - Show Transferred Permissions
* @copyright (c) 2020 - HiFiKabin
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace hifikabin\showtransferedpermissions\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event listener
 */
class listener implements EventSubscriberInterface
{

	public static function getSubscribedEvents()
	{
		return [
			'core.user_setup'				=> 'load_language_on_setup',
		];
	}
	
	public function load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = [
			'ext_name' => 'hifikabin/showtransferedpermissions',
			'lang_set' => 'showtransferedpermissions',
		];
		$event['lang_set_ext'] = $lang_set_ext;
	}
}
