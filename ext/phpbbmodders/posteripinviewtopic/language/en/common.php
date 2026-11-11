<?php
/**
 *
 * Poster IP in Viewtopic extension for the phpBB Forum Software package
 *
 * @copyright (c) 2026, phpBB Modders, https://www.phpbbmodders.com/
 * @copyright (c) 2016, Rich McGirr (RMcGirr83)
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, [
	'ERROR_FROM_SERVER'				=> 'Error was returned from server: %1$s',
	'EXTENSION_REQUIRES_JAVASCRIPT'	=> 'You must have javascript enabled to see the results.',

	'IP_ADDRESS_INVALID'	=> 'Invalid IP address',

	'NO_RESPONSE_FROM_SERVER'	=> 'Failed to get an adequate response from the ip-api server',

	'PAGE_TITLE'		=> 'IP Lookup Results',

	'PIPIV_STATUS'		=> '<b>Status:</b> %1s',
	'PIPIV_QUERY'		=> '<b>IP:</b> %1$s',
	'PIPIV_COUNTRYCODE'	=> '<b>Country Code:</b> %1$s',
	'PIPIV_COUNTRY'		=> '<b>Country Name:</b> %1$s',
	'PIPIV_REGION'		=> '<b>Region Code:</b> %1$s',
	'PIPIV_REGIONNAME'	=> '<b>Region Name:</b> %1$s',
	'PIPIV_CITY'		=> '<b>City:</b> %1$s',
	'PIPIV_ZIP'			=> '<b>Zip Code:</b> %1$s',
	'PIPIV_TIMEZONE'	=> '<b>Time Zone:</b> %1$s',
	'PIPIV_LAT'			=> '<b>Latitude:</b> %1$s',
	'PIPIV_LON'			=> '<b>Longitude:</b> %1$s',
	'PIPIV_ISP'			=> '<b>Provider:</b> %1$s',
	'PIPIV_MESSAGE'		=> '<b>Message:</b> %1$s',
]);
