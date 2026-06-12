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
	'SPAMSECURE_INVALID_CHARS_WARNING'				=> 'Die Nachricht enthält die nachfolgende Anzahl unerlaubter Zeichen/Zeichenketten: ',
	'SPAMSECURE_INVALID_CHARS_WARNING_COMPLETE'		=> 'Mindestens eins der Eingabefelder enthält unerlaubte Zeichen/Zeichenketten: ',
	'SPAMSECURE_INVALID_CHARS_INFO'					=> '<br>Folgende Zeichen/Zeichenketten sind nicht erlaubt: ',
	'SPAMSECURE_INVALID_REGEX_WARNING'				=> 'In der Nachricht sind nicht erlaubte reguläre Ausdrücke (z.B. Links)!',
	'SPAMSECURE_INVALID_WARNING_ALL_FIELDS'			=> 'Mindestens eins der Eingabefelder enthält nicht erlaubte reguläre Ausdrücke (z.B. Links)!',
]);
