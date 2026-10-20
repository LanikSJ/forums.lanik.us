<?php
/**
 *
 * Maintenance extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 stoker - https://phpbb3bbcodes.com/
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
	'STOKER_MAINTENANCE_DEFAULT_HEADING'	=> 'The board is currently undergoing maintenance',
	'STOKER_MAINTENANCE_DEFAULT_MESSAGE'	=> 'We’ll be back shortly. Thanks for your patience.',
	'STOKER_MAINTENANCE_PAGE_TITLE'			=> 'Board Under Maintenance',
	'STOKER_MAINTENANCE_OPENING'			=> 'We are opening again in',

	'STOKER_MAINTENANCE_DAYS'				=> 'Days',
	'STOKER_MAINTENANCE_HOURS'				=> 'Hours',
	'STOKER_MAINTENANCE_MINUTES'			=> 'Minutes',
	'STOKER_MAINTENANCE_SECONDS'			=> 'Seconds',
	'STOKER_MAINTENANCE_PROGRESS_LABEL'		=> 'Maintenance progress',
	'STOKER_MAINTENANCE_WINDOW_START'		=> 'Maintenance start',
	'STOKER_MAINTENANCE_WINDOW_END'			=> 'Estimated end',
	'STOKER_MAINTENANCE_BANNER_WINDOW'		=> 'Scheduled maintenance from <strong>%1$s</strong> until <strong>%2$s</strong>',
	'STOKER_MAINTENANCE_BANNER_PLANNED'		=> 'Scheduled maintenance begins <strong>%s</strong>',
	'STOKER_MAINTENANCE_BANNER_READ_MORE'	=> 'Read the announcement!',
	'STOKER_MAINTENANCE_BANNER_CLOSING'		=> 'The board is closing for maintenance in <strong>%s</strong>',

	'STOKER_MAINTENANCE_CLOSING'			=> 'Board closing for maintenance',
	'STOKER_MAINTENANCE_REOPENING'			=> 'Board re-opening. Please wait',
]);
