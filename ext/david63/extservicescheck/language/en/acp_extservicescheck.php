<?php
/**
*
* @package Extension .yml Check Extension
* @copyright (c) 2019 david63
* @license GNU General Public License, version 2 (GPL-2.0)
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
	$lang = [];
}

/// DEVELOPERS PLEASE NOTE
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
	'COMMON_ERRORS'					=> 'Common errors',
	'COMMON_ERRORS_EXPLAIN'			=> 'Some of the common reasons for the errors reported above may be due to, but not limited to, the following reasons:<hr>- A single quote is missing when preceeding and/or following a line starting with either “@” or “%”.<br>- “pattern” has been replaced with “path”.<br>- “scope: prototype” has become “shared: false”.<br>- “scope: container” has become “shared: true”.<br>- “scope: request” has also been changed but needs further checking as to what the repacement is”.<br>- An extension’s namespace will be reported as invalid if it contains an underscore.<br>- A <em>services.yml</em> file is not “public”.<hr>If in doubt about what to change, or how to change it, then firstly check to see if there is an updated version of the extension available and if not then contact the extension developer in the support topic for the extension.',
	'CONFIG_FILE_FAIL'				=> '<strong>The <em>%s</em> file is invalid</strong>',
	'CONFIG_FILE_PASS'				=> 'The <em>%s</em> file is valid',
	'COPY_CLIPBOARD'				=> 'Copy to clipboard',

	'DISABLED'						=> 'Disabled',
	'DISABLE_EXPLAIN'				=> 'Clicking here will disable this extension.',
	'DORMANT'						=> 'Dormant',

	'ENABLED'						=> 'Enabled',
	'ERROR_EXPLAIN'					=> 'Indicates an extension where one, or more, of the .yml files appears to contain invalid syntax.',
	'EXTENSION_NAME'				=> 'Extension name',
	'EXTENSION_QUERY_EXPLAIN'		=> 'There is a query with one, or more, files in this extension.',
	'EXT_SERVICES_CHECK'			=> 'Extensions .yml Files Syntax Check',
	'EXT_SERVICES_CHECK_EXPLAIN'	=> 'This extension will check the syntax of all of the <em>.yml</em> files, for all of the extensions on this board, for current and future compatibility with phpBB.<br>A check will also be made on the extension’s namespace for current and future compatiity.
  <br><br>The results from this extension will <strong>NOT</strong> guarantee that the extension is compatible in any other way with the current, or future, versions of phpBB nor does it guarantee that there are no other issues with the <em>.yml</em> files.<br><br>Note: A “Dormant” extension is one that is in the <em>ext</em> folder but is neither Enabled nor Disabled.',
	'EXT_STATUS'					=> 'Extension status',

	'FILE_EMPTY'					=> 'The <em>%s</em> file is empty',
	'FILE_ERROR'					=> 'The following error was detected when accessing %1$s<br>Error: %2$s',
	'FILE_EXPLAIN'					=> 'Clicking here will open the “before” and “after” of the file formatting.',
	'FILE_NOT_ACCESSIBLE'			=> 'The <em>%s</em> file is not accessible',
	'FILE_NOT_READABLE'				=> 'The <em>%s</em> file is not readable',
	'FILE_OPEN_EXPLAIN'				=> 'Clicking here will close the “before” and “after” of the file formatting.',
	'FILE_QUERY_EXPLAIN'			=> 'There is a query with this file.',

	'ICON_EXTN_DISABLE'				=> 'Disable this extension',
	'ICON_EXTN_ERROR'				=> 'This extension appears to have errors',
	'ICON_EXTN_ISSUE'				=> 'There is a potential issue with this extension',
	'ICON_EXTN_OK'					=> 'This extension appers to be correct',
	'ICON_EXTN_QUERY'				=> 'There is a query with one, or more, files in this extension',
	'ICON_FILE_CLOSED'				=> 'Click to show files',
	'ICON_FILE_ISSUE'				=> 'This file has a potential issue',
	'ICON_FILE_OPEN'				=> 'Click to close files',
	'ICON_FILE_QUERY'				=> 'There is a query with this file',
	'INVALID_CHRACTERS'				=> 'The namespace <em>%s</em> contains invalid characters',
	'ISSUE_EXPLAIN'					=> 'Indicates that this extension may have a potential problem in a future version.',
	'ISSUE_OPEN_EXPLAIN'			=> 'Clicking this icon will show the potential issue with this file.',

	'LEGEND'						=> 'Legend',

	'NEW_FILE'						=> 'Reformatted file',
	'NO_CONFIG_FILES'				=> 'This extension does not have any config files',
	'NOT_PUBLIC'					=> '<strong>The <em>%s</em> file is not public</strong>',

	'OK_EXPLAIN'					=> 'This extensions appears to be OK.',
	'ORIGINAL_FILE'					=> 'Original file',

	'REQUIRES_ATTENTION'			=> '&nbsp;#This line requires attention',

	'STATUS'						=> '.yml File Status',

	'VENDOR'						=> 'Vendor',
));
