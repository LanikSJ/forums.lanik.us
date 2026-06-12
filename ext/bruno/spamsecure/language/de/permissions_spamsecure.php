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
	'ACL_U_VIEW_SPAMSECURE_INVALID_CHARS'		=> 'Spamsecure-Einstellung: Kann Themen, Beiträge oder Kontaktanfragen ohne Prüfung auf unerlaubte Zeichen/Zeichenketten erstellen',
	'ACL_U_VIEW_SPAMSECURE_INVALID_REGEX'		=> 'Spamsecure-Einstellung: Kann Themen, Beiträge oder Kontaktanfragen ohne Prüfung auf unerlaubte Ausdrücke (RegEx-Codierung) erstellen',
]);
