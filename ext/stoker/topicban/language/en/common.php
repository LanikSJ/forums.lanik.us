<?php
/**
 *
 * Topic Ban extension for the phpBB Forum Software package.
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
	// Viewtopic, posting, reporting
	'TOPICBAN_NOTICE'				=> 'You have been permanently banned from posting in this topic.',
	'TOPICBAN_NOTICE_UNTIL'			=> 'You have been banned from posting in this topic until %s.',
	'TOPICBAN_REASON_LABEL'			=> 'Reason',
	'TOPICBAN_POSTING_DENIED'		=> 'You have been permanently banned from posting in this topic.',
	'TOPICBAN_POSTING_DENIED_UNTIL'	=> 'You have been banned from posting in this topic until %s.',
	'TOPICBAN_REPORT_DENIED'		=> 'You have been banned from this topic and cannot report posts in it.',
	'TOPICBAN_ACTION_DENIED'		=> 'You have been banned from this topic and cannot subscribe to or bookmark it.',
	'TOPICBAN_EMAIL_DENIED'			=> 'You have been banned from this topic and cannot email it to anyone.',
	'TOPICBAN_POST_NOTICE'			=> '%s has been permanently banned from posting in this topic.',
	'TOPICBAN_POST_NOTICE_UNTIL'	=> '%1$s has been banned from posting in this topic until %2$s.',

	// MCP post details
	'TOPICBAN_TITLE'				=> 'Topic ban',
	'TOPICBAN_LENGTH'				=> 'Ban length',
	'TOPICBAN_PERMANENT'			=> 'Permanent',
	'TOPICBAN_1_HOUR'				=> '1 hour',
	'TOPICBAN_6_HOURS'				=> '6 hours',
	'TOPICBAN_1_DAY'				=> '1 day',
	'TOPICBAN_7_DAYS'				=> '7 days',
	'TOPICBAN_30_DAYS'				=> '30 days',
	'TOPICBAN_UNTIL_DATE'			=> 'Until date and time',
	'TOPICBAN_REASON'				=> 'Reason',
	'TOPICBAN_REASON_EXPLAIN'		=> 'Shown to the banned user.',
	'TOPICBAN_BAN'					=> 'Ban from topic',
	'TOPICBAN_UPDATE'				=> 'Update ban',
	'TOPICBAN_UNBAN'				=> 'Remove ban',
	'TOPICBAN_STATUS_NONE'			=> 'The poster is not banned from this topic.',
	'TOPICBAN_STATUS_PERMANENT'		=> 'The poster is permanently banned from this topic.',
	'TOPICBAN_STATUS_UNTIL'			=> 'The poster is banned from this topic until %s.',
	'TOPICBAN_STATUS_PROTECTED'		=> 'The poster is an administrator or moderator of this forum and cannot be banned from topics.',
	'TOPICBAN_STATUS_PROTECTED_STALE'	=> 'The poster is an administrator or moderator of this forum. The existing topic ban has no effect and can be removed.',
	'TOPICBAN_ADDED'				=> 'The user has been banned from the topic.',
	'TOPICBAN_UPDATED'				=> 'The topic ban has been updated.',
	'TOPICBAN_REMOVED'				=> 'The topic ban has been removed.',
	'TOPICBAN_ERROR_PROTECTED'		=> 'This user cannot be banned from topics.',
	'TOPICBAN_ERROR_NOT_BANNED'		=> 'This user is not banned from the topic.',
	'TOPICBAN_ERROR_LENGTH'			=> 'The selected ban length is invalid.',
	'TOPICBAN_ERROR_DATE'			=> 'Enter a valid date and time in the future.',

	// MCP ban list
	'MCP_TOPICBAN_LIST_EXPLAIN'		=> 'Active topic bans in the forums you moderate. Bans are added and edited from the post details page of a post by the user.',
	'TOPICBAN_TOPIC'				=> 'Topic',
	'TOPICBAN_USER'					=> 'User',
	'TOPICBAN_BANNED_BY'			=> 'Banned by',
	'TOPICBAN_DELETED_USER'			=> 'Deleted user',
	'TOPICBAN_BAN_TIME'				=> 'Banned',
	'TOPICBAN_BAN_END'				=> 'Expires',
	'TOPICBAN_NO_BANS'				=> 'There are no active topic bans.',
	'TOPICBAN_UNBAN_MARKED'			=> 'Remove marked bans',
	'TOPICBAN_ERROR_NONE_MARKED'	=> 'No bans were selected.',
	'TOPICBAN_TOTAL'				=> [
		0	=> 'No topic bans',
		1	=> '%d topic ban',
		2	=> '%d topic bans',
	],
	'TOPICBAN_REMOVED_COUNT'		=> [
		0	=> 'No topic bans were removed.',
		1	=> '%d topic ban has been removed.',
		2	=> '%d topic bans have been removed.',
	],

	// Notification
	'NOTIFICATION_TYPE_TOPICBAN'	=> 'You are banned from posting in a topic',
	'NOTIFICATION_TOPICBAN'			=> '<strong>Topic ban:</strong> You have been permanently banned from posting in “%s”.',
	'NOTIFICATION_TOPICBAN_UNTIL'	=> '<strong>Topic ban:</strong> You have been banned from posting in “%1$s” until %2$s.',
]);
