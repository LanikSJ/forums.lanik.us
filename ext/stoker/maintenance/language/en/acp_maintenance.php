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
	'ACP_STOKER_BADGE'								=> 'Board Maintenance',
	'ACP_STOKER_SETTINGS_EXPLAIN'					=> 'If you like this extension, please consider following',
	'ACP_STOKER_DONATION'							=> 'Make a Donation',
	'ACP_STOKER_MEMBER'								=> 'Become an active member of my Community',
	'ACP_STOKER_SUPPORT'							=> 'Extension support or feedback',

	'ACP_STOKER_MAINTENANCE_GENERAL'				=> 'General',
	'ACP_STOKER_MAINTENANCE_ENABLED'				=> 'Enable extension',
	'ACP_STOKER_MAINTENANCE_ENABLED_EXPLAIN'		=> 'With this off the extension does nothing at all: no maintenance page, no schedule, no banner. The board falls back to phpBB’s own message, set under General » Board Settings.<br>Switching it off also clears any schedule, and switches “Disable board” back off if - and only if - it was this extension’s schedule that switched it on. A board you took offline yourself stays offline.<br>Your settings below are kept and take effect again as soon as you switch it back on.',
	'ACP_STOKER_MAINTENANCE_PREVIEW'				=> 'Preview',
	'ACP_STOKER_MAINTENANCE_PREVIEW_EXPLAIN'		=> 'Shows the maintenance page using your current settings. Nothing is changed: the board stays as it is and the schedule is untouched.<br>If your schedule has an end time still ahead, the preview shows it exactly as visitors will see it. In every other case - no schedule, no end time, or an end time that has already passed - the preview counts down a five-minute window of its own so you can see the timer and progress bar in action.<br>The link works for administrators only - opening it as anyone else just shows the board as normal.',
	'ACP_STOKER_MAINTENANCE_PREVIEW_LINK'			=> 'Open the maintenance page in a new tab',

	'ACP_STOKER_MAINTENANCE_DISABLE_BOARD'			=> 'Disable board',
	'ACP_STOKER_MAINTENANCE_DISABLE_BOARD_EXPLAIN'	=> 'Switches the board offline immediately.<br>This is the same setting as General » Board Settings » Disable board - changing it here changes it there too.<br>Switching the board back on ends a maintenance window that is already running. A window that has not started yet is left in place.',
	'ACP_STOKER_MAINTENANCE_DISABLE_BOARD_CONFIRM'	=> 'Visitors will immediately see the maintenance page. Are you sure you want to disable the board?',

	'ACP_STOKER_MAINTENANCE_SCHEDULE'				=> 'Schedule',
	'ACP_STOKER_MAINTENANCE_START'					=> 'Maintenance start',
	'ACP_STOKER_MAINTENANCE_START_EXPLAIN'			=> 'When the maintenance window begins. “Disable board” is switched on at this time - on the first page view after it, so a quiet board may take a moment.<br>Leave blank to run nothing on a schedule: you then switch the board off yourself and no banner is shown.',
	'ACP_STOKER_MAINTENANCE_END'					=> 'Maintenance end',
	'ACP_STOKER_MAINTENANCE_END_EXPLAIN'			=> 'When the maintenance window ends, and “Disable board” is switched back off again. Must be after the start time.<br>Leave blank if you don’t know how long the work will take: the board then stays off until you switch it back on yourself, so overrunning work is never cut short. Visitors see no countdown or progress bar in that case, since there is nothing to count down to.<br>Either way the schedule is cleared once it has run.',

	'ACP_STOKER_MAINTENANCE_CONTENT'				=> 'Message',
	'ACP_STOKER_MAINTENANCE_HEADING'				=> 'Heading',
	'ACP_STOKER_MAINTENANCE_HEADING_EXPLAIN'		=> 'Shown at the top of the maintenance page.',
	'ACP_STOKER_MAINTENANCE_MESSAGE'				=> 'Body text',
	'ACP_STOKER_MAINTENANCE_MESSAGE_EXPLAIN'		=> 'Shown below the heading on the maintenance page.',

	'ACP_STOKER_MAINTENANCE_BANNER'					=> 'Pre-maintenance banner',
	'ACP_STOKER_MAINTENANCE_BANNER_ENABLED'			=> 'Show banner',
	'ACP_STOKER_MAINTENANCE_BANNER_ENABLED_EXPLAIN'	=> 'Displays a notice near the top of every page in the lead-up to the maintenance window.',
	'ACP_STOKER_MAINTENANCE_BANNER_LEAD'			=> 'Show banner (hours before start)',
	'ACP_STOKER_MAINTENANCE_BANNER_LEAD_EXPLAIN'	=> 'How many hours before the maintenance start time the banner should appear.<br>Set to 0 to show it as soon as a start time is set.',
	'ACP_STOKER_MAINTENANCE_BANNER_URL'				=> 'Announcement link',
	'ACP_STOKER_MAINTENANCE_BANNER_URL_EXPLAIN'		=> 'Adds a “Read more” link to the banner.<br>Full address of a topic explaining the maintenance, for example https://example.com/viewtopic.php?t=123.<br>Leave blank for no link.',
	'ACP_STOKER_MAINTENANCE_BANNER_PREVIEW'			=> 'Preview banner',
	'ACP_STOKER_MAINTENANCE_BANNER_PREVIEW_EXPLAIN'	=> 'Shows the pre-maintenance banner on the board’s front page, using your current settings. Nothing is changed: the schedule is untouched and no visitor but you sees it.<br>If no maintenance window is set, or the one you have set has already opened, the preview counts down to a window invented an hour from now, so you can see the banner in its everyday form.<br>The link works for administrators only - opening it as anyone else just shows the board as normal.',
	'ACP_STOKER_MAINTENANCE_BANNER_PREVIEW_LINK'	=> 'Open the board with the banner shown',

	'ACP_STOKER_TOGGLE_ERROR'	=> 'The toggle request failed. Please try again.',

	'ACP_STOKER_MAINTENANCE_VC_OK'					=> 'You are running the latest version (%s)',
	'ACP_STOKER_MAINTENANCE_VC_OUTDATED'			=> 'A new version is available: %2$s (you have %1$s). Click to download.',
	'ACP_STOKER_MAINTENANCE_VC_UNAVAILABLE'			=> 'Version check unavailable',

	'STOKER_MAINTENANCE_ERROR_END_BEFORE_START'		=> 'The maintenance end time must be after the start time.',
	'STOKER_MAINTENANCE_ERROR_END_WITHOUT_START'	=> 'An end time on its own has no effect. Either set a start time as well, or leave the end time blank.',
	'STOKER_MAINTENANCE_ERROR_INVALID_URL'			=> 'The announcement link must be a full address beginning with http:// or https://.',
	'STOKER_MAINTENANCE_ERROR_INVALID_DATE'			=> 'The maintenance start or end time could not be read. Please enter a valid date and time.',
	'STOKER_MAINTENANCE_ERROR_TOO_LONG'				=> 'The heading or the announcement link is too long to store. Emoji take up more space than they appear to - try shortening the text or using fewer of them.',
	'STOKER_MAINTENANCE_ERROR_MESSAGE_TOO_LONG'		=> 'The body text is too long to store. Emoji take up more space than they appear to - try shortening the text or using fewer of them.',
]);
