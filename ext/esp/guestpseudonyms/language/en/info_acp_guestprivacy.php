<?php
/**
 *
 * Guest pseudonyms. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Emma Laurijssens van Engelenhoven
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

$lang = array_merge($lang, [
	'ACP_GUESTPRIVACY_TITLE'      => 'Guest pseudonyms',
	'ACP_GUESTPRIVACY_SETTINGS'   => 'Settings',
	'ACP_GUESTPRIVACY_EXPLAIN'    => 'Control how members’ usernames are hidden from guests and search engines, and which choices members have over their own guest nickname.',

	'GP_ENABLE'                   => 'Enable guest pseudonyms',
	'GP_ENABLE_EXPLAIN'           => 'When enabled, every visitor who is not logged in — guests and search-engine bots alike — sees a bogus nickname in place of each member’s username.',
	'GP_RESOLVE_NAMES'            => 'Resolve quote and mention names from the account',
	'GP_RESOLVE_NAMES_EXPLAIN'    => 'For logged-in members, quotes and @mentions show the referenced member’s current username, resolved from their account rather than the name stored in the post. Renaming a user (for example to Anon20260101 on an account-deletion request) then updates old quotes and mentions automatically, with no database edits. When off, quotes keep the name as it was written.',

	'GP_MEMBER_CONTROLS'          => 'Member choices',
	'GP_ALLOW_REGENERATE'         => 'Allow members to regenerate their nickname',
	'GP_ALLOW_REGENERATE_EXPLAIN' => 'Members can generate a different guest nickname from their User Control Panel.',
	'GP_ALLOW_OPT_OUT'            => 'Allow members to reveal themselves',
	'GP_ALLOW_OPT_OUT_EXPLAIN'    => 'Let members choose, on their Name privacy page, to show their real username to guests and search engines — and, if they wish, their signature too. When off, every member stays protected (pseudonym, signature hidden) with no choice.',
	'GP_REMINDER_MONTHS'          => 'Opt-out reminder interval (months)',
	'GP_REMINDER_MONTHS_EXPLAIN'  => 'Remind opted-out members this often that their real name is public. Set to 0 to disable reminders.',
	'GP_AUTOREVERT'               => 'Re-protect if a reminder is ignored',
	'GP_AUTOREVERT_EXPLAIN'       => 'If an opted-out member does not re-confirm after a reminder, automatically hide their name again (fail-closed).',

	'GUESTPSEUDONYMS_ACP_SAVED'   => 'Guest pseudonyms settings have been saved.',

	// Admin log entry written when these settings are saved.
	'LOG_GUESTPSEUDONYMS_SETTINGS' => 'Guest pseudonyms settings changed',
]);
