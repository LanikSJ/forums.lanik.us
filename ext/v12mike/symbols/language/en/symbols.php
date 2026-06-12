<?php
/**
*
* Symbols Extension [English]
*
* @package language Symbols
* @copyright (c)  2018 v12mike
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'SYM_ACCENTED_CHARS'	=> 'Accented Characters',
	'SYM_MATH_SYMBOLS'		=> 'Math Symbols',
	'SYM_MISC_SYMBOLS'		=> 'Miscellaneous Symbols',
	'SYM_GREEK_LETTERS'		=> 'Greek Letters',
	'SYM_SMILIES'			=> 'Smilies',
));
