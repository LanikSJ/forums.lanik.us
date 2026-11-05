<?php
/**
 *
 * Emoji V2 extension for the phpBB Forum Software package.
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
	// Header
	'ACP_STOKER_BADGE'						=> 'Emoji V2',
	'ACP_STOKER_SETTINGS_EXPLAIN'			=> 'If you like this extension, please consider following',
	'ACP_STOKER_DONATION'					=> 'Make a Donation',
	'ACP_STOKER_MEMBER'						=> 'Become an active member of my Community',
	'ACP_STOKER_SUPPORT'					=> 'Extension support or feedback',
	'ACP_EMOJI_EXPLAIN'						=> 'In <strong>Emoji</strong> mode, rendering depends on each visitor’s operating system and browser.<br>Older Android, Windows 7/8, or out-of-date browsers may show some emoji as blank boxes, so favour common emoji (smiley faces, hearts, basic symbols) for the widest reach.<br>In <strong>Google Noto Color Emoji</strong> and <strong>Twemoji</strong> mode, the bundled font renders the same way for everyone. The default font holds the most used emoji; any other emoji is shown in the visitor’s own system style unless you select the full font.<br><br>All emoji modes switch off phpBB’s board-wide <em>Allow smilies</em> setting, since emoji take the place of smilies. Disabling this extension does <strong>not</strong> switch it back on again - set <em>Allow smilies</em> to <em>Yes</em> under <em>General → Board configuration → Board features</em> if you want your smilies back. Uninstalling with <em>Delete data</em> restores the setting automatically.<br><br>All emoji modes also remove the smiley box from the signature editor. Smiley parsing in signatures is controlled separately by <em>Allow use of smilies in user signatures</em> under <em>General → Board configuration → Signature settings</em> - while it is <em>Yes</em>, a smiley code typed in a signature without a matching emoji shortcode still turns into a smilie.',

	// Toggle / dropdown errors
	'ACP_STOKER_TOGGLE_ERROR'				=> 'The toggle request failed. Please try again.',

	// Emoji mode dropdown
	'ACP_STOKER_EMOJI_SMILIES'				=> 'Replace smilies with emoji',
	'ACP_STOKER_EMOJI_MODE'					=> 'Emoji mode',
	'ACP_STOKER_EMOJI_MODE_EXPLAIN'			=> 'Choose how emoji are handled board-wide.<br><br><strong>Smilies</strong> - use phpBB’s classic smilies (default).<br><br><strong>Emoji</strong> - replace smilies with the emoji set in the Manage emojis module. Rendering relies on each visitor’s operating system and browser.<br><br><strong>Google Noto Color Emoji</strong> - same as Emoji, but also loads the bundled Noto Color Emoji font so everyone sees Google’s emoji style regardless of their device or browser.<br><br><strong>Twemoji</strong> - same as Emoji, but also loads the bundled Twemoji font so everyone sees the Twemoji style regardless of their device or browser.',

	'ACP_STOKER_EMOJI_MODE_SMILIES'			=> 'Smilies (default)',
	'ACP_STOKER_EMOJI_MODE_EMOJI'			=> 'Emoji',
	'ACP_STOKER_EMOJI_MODE_NOTO'			=> 'Google Noto Color Emoji',
	'ACP_STOKER_EMOJI_MODE_TWEMOJI'		=> 'Twemoji',

	// Emoji font file
	'ACP_STOKER_EMOJI_NOTO_FONT'			=> 'Emoji font file',
	'ACP_STOKER_EMOJI_NOTO_FONT_EXPLAIN'	=> 'The font loaded in Google Noto Color Emoji and Twemoji mode. Either file is downloaded once and then cached by the browser.<br><br><strong>Most used emoji</strong> - Noto about 500 KB, Twemoji about 70 KB (default). Emoji not in it are shown in the visitor’s own system style.<br><br><strong>All emoji</strong> - every emoji in the chosen style. Noto about 5.6 MB, Twemoji about 630 KB.',
	'ACP_STOKER_EMOJI_NOTO_FONT_COMMON'		=> 'Most used emoji (default)',
	'ACP_STOKER_EMOJI_NOTO_FONT_FULL'		=> 'All emoji',

	// Emoji size
	'ACP_STOKER_EMOJI_SIZING'				=> 'Emoji resizing',
	'ACP_STOKER_EMOJI_SIZE'					=> 'Emoji size',
	'ACP_STOKER_EMOJI_SIZE_EXPLAIN'			=> 'Display size of emoji in posts when using the visitor’s own emoji (Emoji mode).<br>1.3rem is the default.',
	'ACP_STOKER_EMOJI_NOTO_SIZE'			=> 'Emoji font size',
	'ACP_STOKER_EMOJI_NOTO_SIZE_EXPLAIN'	=> 'Display size of emoji in posts when a bundled emoji font is used (Google Noto Color Emoji and Twemoji mode).<br>The bundled fonts run slightly larger, so 1.2rem is the default.',

	// Quick reply
	'ACP_STOKER_EMOJI_QR'					=> 'Quick Reply',
	'ACP_STOKER_EMOJI_QUICKREPLY'			=> 'Show emoji picker in quick reply',
	'ACP_STOKER_EMOJI_QUICKREPLY_EXPLAIN'	=> 'When enabled, the emoji picker is shown in the quick reply editor at the bottom of topic pages.<br>When disabled, the picker only appears on the full posting page.',

	// Emoji list
	'ACP_STOKER_EMOJI_LIST'					=> 'Emojis',
	'ACP_STOKER_EMOJI_LIST_EXPLAIN'			=> 'Each row is one emoji. List the shortcodes that produce it, separated by spaces. These are replaced by the emoji when a post is sent, exactly like smilies.<br>Tick "On posting page" to show the emoji directly in the posting editor and quick reply. Un-ticked emojis are still available from the "More" popup.<br>Use the arrows to set the display order.',
	'ACP_STOKER_EMOJI_CHAR'					=> 'Emoji',
	'ACP_STOKER_EMOJI_CODES'				=> 'Shortcodes',
	'ACP_STOKER_EMOJI_EMOTION'				=> 'Emotion',
	'ACP_STOKER_EMOJI_POSTING'				=> 'On posting page',
	'ACP_STOKER_EMOJI_ORDER'				=> 'Order',
	'ACP_STOKER_EMOJI_MOVE_UP'				=> 'Move up',
	'ACP_STOKER_EMOJI_MOVE_DOWN'			=> 'Move down',
	'ACP_STOKER_EMOJI_ADD'					=> 'Add emoji',
	'ACP_STOKER_EMOJI_REMOVE'				=> 'Remove emoji',

	// Confirmations
	'ACP_STOKER_EMOJI_SAVED'				=> 'The emojis have been saved successfully.',
	'ACP_STOKER_EMOJI_SETTINGS_SAVED'		=> 'The settings have been saved successfully.',
	'ACP_STOKER_EMOJI_TRUNCATED'			=> 'The emojis were not saved because the list has more fields than the server accepts in one request (PHP max_input_vars is %d). Remove some rows, or ask your host to raise max_input_vars.',
	'ACP_STOKER_EMOJI_SKIPPED_EMOJI'		=> [
		1	=> '%d emoji was not saved because it repeated another row or was too long.',
		2	=> '%d emojis were not saved because they repeated another row or were too long.',
	],
	'ACP_STOKER_EMOJI_SKIPPED_CODES'		=> [
		1	=> '%d shortcode was not saved because it is already used by another emoji or did not fit.',
		2	=> '%d shortcodes were not saved because they are already used by another emoji or did not fit.',
	],

	// Version check
	'ACP_STOKER_EMOJI_VC_OK'				=> 'You are running the latest version (%s)',
	'ACP_STOKER_EMOJI_VC_OUTDATED'			=> 'A new version is available: %2$s (you have %1$s). Click to download.',
	'ACP_STOKER_EMOJI_VC_UNAVAILABLE'		=> 'Version check unavailable',

	// Requirements
	'EMOJI_REQUIRE_PHPBB'					=> 'The Emoji extension requires phpBB %1$s or later. Your version is %2$s.',
	'EMOJI_REQUIRE_PHP'						=> 'The Emoji extension requires PHP %1$s or later. Your version is %2$s.',
	'EMOJI_REQUIRE_REMOVE'					=> 'The Emoji extension version 1.x must be uninstalled (Delete data) before this version can be enabled.<br>Get support here: <a href="https://phpbb3bbcodes.com/emoji-t3731" target="_blank" rel="noopener noreferrer"><strong>Emoji Support</strong></a>',
]);
