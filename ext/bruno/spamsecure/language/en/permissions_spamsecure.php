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
	'ACL_U_VIEW_SPAMSECURE_INVALID_CHARS'		=> 'Spamsecure setting: <b>"Can create topics, posts or contact requests without checking for disallowed characters/characters"</b>',
	'ACL_U_VIEW_SPAMSECURE_INVALID_REGEX'		=> 'Spamsecure setting: <b>"Can create topics, posts or contact requests without checking for disallowed expressions ("RegEx encoding")</b>',
]);
