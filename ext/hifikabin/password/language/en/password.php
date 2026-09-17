<?php
/**
*
* @package phpBB Extension - Password Generator
* @copyright (c) 2019 HiFiKabin
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
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

$lang = array_merge($lang, array(

	'PASSWORD_GENERATOR'				=> 'Password Generator',
	'PASSWORD_LINK'						=> 'Password Generator',
	'PASSWORD_TITLE'					=> 'Random Password Generator',
	'PASSWORD_CREATE_INSTRUCTIONS'		=> 'Clicking “Create” will generate a random password.',
	'PASSWORD_COPY_INSTRUCTIONS'		=> 'Clicking “Copy” will copy that password to your computer’s clipboard.',
	'PASSWORD_CREATE'					=> 'Create',
	'PASSWORD_COPY'						=> 'Copy',
	'PASSWORD_REMINDER_1'				=> 'Remember to save the generated password before submitting this page.',
	'PASSWORD_REMINDER_2'				=> 'Remember to save the generated password before leaving this page.',
	'PASSWORD_NOSCRIPT'					=> 'JavaScript needs to be enabled to use the Password Generator.',
));
