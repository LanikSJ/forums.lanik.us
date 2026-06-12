<?php
/**
*
* @package phpBB Extension - Spamsecure from 69bruno
* @copyright (c) 2021 (cruiser-lounge.de)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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

$lang = array_merge($lang, [
	'SPAMSECURE_INVALID_CHARS_WARNING'				=> 'The message contains the following number of disallowed characters/strings: ',
	'SPAMSECURE_INVALID_CHARS_WARNING_COMPLETE'		=> 'At least one of the input fields contains illegal characters/character strings: ',
	'SPAMSECURE_INVALID_CHARS_INFO'					=> '<br>The following characters/character strings are not permitted: ',
	'SPAMSECURE_INVALID_REGEX_WARNING'				=> 'The message contains disallowed regular expressions (e.g. links)!',
	'SPAMSECURE_INVALID_WARNING_ALL_FIELDS'			=> 'At least one of the input fields contains non-permitted regular expressions (e.g. links)!',
]);
