<?php
/**
 *
 * URL Rewrite extension for the phpBB Forum Software package.
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
	// Shared header block
	'ACP_STOKER_BADGE'							=> 'User Friendly URLs',
	'ACP_STOKER_SETTINGS_EXPLAIN'				=> 'If you like this extension, please consider following',
	'ACP_STOKER_DONATION'						=> 'Make a Donation',
	'ACP_STOKER_MEMBER'							=> 'Become an active member of my Community',
	'ACP_STOKER_SUPPORT'						=> 'Extension support or feedback',
	'ACP_EC_REPORT'								=> 'phpBB Ext Check Report',
	'ACP_STOKER_TOGGLE_ERROR'					=> 'The toggle request failed. Please try again.',

	// Settings page
	'ACP_URLREWRITE_SETTINGS_EXPLAIN'			=> 'General settings for converting phpBB URLs into human-readable URLs.',
	'ACP_URLREWRITE_IMPORTANT'					=> 'It’s important that you check the <strong>%1$sServer Config page%2$s</strong> and follow the steps described for Apache/NGINX!',

	'STOKER_URLREWRITE_ENABLE'					=> 'Enable URL rewriting',
	'STOKER_URLREWRITE_ENABLE_EXPLAIN'			=> 'Rewrite forum, topic and post links into human-readable URLs. Requires the matching server rules from the Server configuration tab.',

	'STOKER_URLREWRITE_REDIRECT_ENABLE'			=> 'Enable 301 redirects',
	'STOKER_URLREWRITE_REDIRECT_ENABLE_EXPLAIN'	=> 'Permanently redirect direct hits on the default URLs (viewtopic.php, viewforum.php) to their human-readable equivalent. Enable this only once the server rules are working.',

	'STOKER_URLREWRITE_TRANSLIT'				=> 'Transliteration method',
	'STOKER_URLREWRITE_TRANSLIT_EXPLAIN'		=> 'How non-ASCII characters in titles become the URL slug. The slug is cosmetic: links are routed by topic/forum ID, so this never affects whether a URL works, only how it reads.<br><br><b>Off</b> - keep the original characters. Only unsafe characters are replaced.<br>Non-Latin titles stay native but appear percent-encoded in the address bar.<br><br><b>Static</b> (recommended) - a built-in table converts accented Latin characters to ASCII (æ→ae, ø→oe, å→aa, é→e, ü→u …).<br>Identical on every server. Latin scripts only. Non-Latin titles are reduced to the ID.<br><br><b>intl</b> - uses the server’s intl extension to also romanize non-Latin scripts (Привет→privet, Ελληνικά→ellinika).<br>Requires the intl PHP extension. If it is not installed, Static is used automatically.',
	'STOKER_URLREWRITE_TRANSLIT_OFF'			=> 'Off (keep original characters)',
	'STOKER_URLREWRITE_TRANSLIT_STATIC'			=> 'Static (built-in table, recommended)',
	'STOKER_URLREWRITE_TRANSLIT_INTL'			=> 'intl (romanize non-Latin scripts)',
	'STOKER_URLREWRITE_TRANSLIT_INTL_MISSING'	=> 'intl - not installed on this server',

	// Server configuration page
	'ACP_URLREWRITE_SERVER_EXPLAIN'				=> 'Copy the rules for your web server. Nothing is written automatically. <strong>You need to paste these yourself.</strong><br>The paths shown match your current board location. If you move the board or change the server URL settings later, copy the rules again.',
	'STOKER_URLREWRITE_SELECT_SERVER'			=> 'Web server',
	'STOKER_URLREWRITE_SERVER_APACHE'			=> 'Apache (.htaccess)',
	'STOKER_URLREWRITE_SERVER_NGINX'			=> 'NGINX (server block)',

	'STOKER_URLREWRITE_FORWARD_RULES'			=> 'Rewrite rules',
	'STOKER_URLREWRITE_REVERSE_RULES'			=> 'Reverse rules (uninstall)',

	'STOKER_URLREWRITE_APACHE_FORWARD_EXPLAIN'	=> 'Paste into your <code>.htaccess</code>, after <code>RewriteEngine On</code> and before phpBB’s own <code>app.php</code> rewrite rules.',
	'STOKER_URLREWRITE_APACHE_REVERSE_EXPLAIN'	=> 'For uninstalling: these 301-redirect human-readable URLs back to the default phpBB URLs. Paste them ONLY after you have disabled the extension and removed the forward rules - running both at once creates a redirect loop.',
	'STOKER_URLREWRITE_NGINX_FORWARD_EXPLAIN'	=> 'Add inside your <code>server { }</code> block, then reload NGINX. NGINX does not read <code>.htaccess</code>.',
	'STOKER_URLREWRITE_NGINX_REVERSE_EXPLAIN'	=> 'For uninstalling: these 301-redirect human-readable URLs back to the default phpBB URLs. Add them ONLY after you have disabled the extension and removed the forward rules - running both at once creates a redirect loop.',

	// Comment lines inside the generated server rules. Plain text only - these are
	// written into a config file, not into HTML.
	'STOKER_URLREWRITE_RULES_APACHE_FORWARD'	=> 'stoker/urlrewrite - Apache forward rules',
	'STOKER_URLREWRITE_RULES_APACHE_REVERSE'	=> 'stoker/urlrewrite - Apache reverse rules (uninstall)',
	'STOKER_URLREWRITE_RULES_NGINX_FORWARD'		=> 'stoker/urlrewrite - NGINX forward rules',
	'STOKER_URLREWRITE_RULES_NGINX_REVERSE'		=> 'stoker/urlrewrite - NGINX reverse rules (uninstall)',
	'STOKER_URLREWRITE_RULES_UNINSTALL_NOTE'	=> 'Use ONLY after disabling the extension AND removing the forward rules.',
	'STOKER_URLREWRITE_RULES_APACHE_NOTE'		=> 'Place these AFTER "RewriteEngine On" and BEFORE the phpBB app.php rewrite rules.',
	'STOKER_URLREWRITE_RULES_NGINX_NOTE'		=> 'Add inside your server { } block. This does NOT go in .htaccess.',

	'STOKER_URLREWRITE_COPY'					=> 'Copy',
	'STOKER_URLREWRITE_COPIED'					=> 'Copied!',
	'STOKER_URLREWRITE_COPY_FAILED'				=> 'Copy failed - select the text manually.',

	// Version check
	'ACP_URLREWRITE_VC_OK'						=> 'You are running the latest version (%s)',
	'ACP_URLREWRITE_VC_OUTDATED'				=> 'A new version is available: %2$s (you have %1$s). Click to download.',
	'ACP_URLREWRITE_VC_UNAVAILABLE'				=> 'Version check unavailable',

	'URLREWRITE_REQUIRE_PHPBB'					=> 'User Friendly URLs requires phpBB %1$s or later. Your version is %2$s.',
	'URLREWRITE_REQUIRE_PHP'					=> 'User Friendly URLs requires PHP %1$s or later. Your version is %2$s.',
]);
