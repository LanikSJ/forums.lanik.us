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
	// UCP "Name privacy" page (Story #23)
	'UCP_GUESTPRIVACY'                       => 'Name privacy',

	'GUESTPSEUDONYMS_YOUR_NICKNAME'          => 'Your guest nickname',
	'GUESTPSEUDONYMS_NICKNAME_EXPLAIN'       => 'This is the name shown to visitors who are not logged in (and to search engines), in place of your username. You will rarely see it yourself.',
	'GUESTPSEUDONYMS_REGENERATE'             => 'Generate a new nickname',
	'GUESTPSEUDONYMS_REGENERATE_EXPLAIN'     => 'If you would prefer a different nickname, generate a new one. It will be created in your language.',
	'GUESTPSEUDONYMS_VISIBILITY'             => 'What guests and search engines see',
	'GUESTPSEUDONYMS_VISIBILITY_EXPLAIN'     => 'Choose how much of your identity visitors who are not logged in — guests and search engines — can see. Logged-in members always see your real username and signature.',
	'GUESTPSEUDONYMS_LEVEL_1'                => 'Hide my name and signature',
	'GUESTPSEUDONYMS_LEVEL_2'                => 'Show my real username, but hide my signature',
	'GUESTPSEUDONYMS_LEVEL_3'                => 'Show my real username and signature',

	'GUESTPSEUDONYMS_SAVED'                  => 'Your name privacy settings have been saved.',
	'GUESTPSEUDONYMS_REGENERATED'            => 'A new guest nickname has been generated for you.',

	// Opt-out reminder banner (Story #24)
	'GUESTPSEUDONYMS_REMINDER_TITLE'         => 'Reminder:',
	'GUESTPSEUDONYMS_REMINDER'               => 'your real username is currently shown to guests and search engines.',
	'GUESTPSEUDONYMS_REMINDER_LINK'          => 'Review your name privacy settings',

	// Console command (bin/phpbbcli.php esp:guestpseudonyms:backfill)
	'CLI_DESCRIPTION_GUESTPSEUDONYMS_BACKFILL' => 'Generate a guest nickname for every member that does not have one yet.',
	'CLI_GUESTPSEUDONYMS_BACKFILL_DONE'      => [
		0 => 'Every member already has a guest nickname.',
		1 => 'Generated %d guest nickname.',
		2 => 'Generated %d guest nicknames.',
	],
]);
