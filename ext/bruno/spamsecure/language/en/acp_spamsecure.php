<?php
/**
*
* @package phpBB Extension - Spamsecure from 69bruno
* @copyright (c) 2021 (cruiser-lounge.de)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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
	// Information For Versionscheck Metadaten
	'SPSE_LANG_EXT_VER' 						=> '1.0.6',
	'SPSE_LANG_DESC'							=> 'English (GB)',
	'SPSE_LANG_AUTHOR' 							=> '69bruno',
	'SPSE_CONFIG_DESC' 							=> 'Here the settings for the extension <b>„%1$s“ (v%2$s)</b> can be changed.',
	'SPSE_LANGUAGEPACK_OUTDATED'				=> 'Note: The language pack of this extension is out of date.',
	'SPSE_SPAMSECURE_INFO'						=> 'The extension checks created posts and topics before publication for characters, character strings and regular expressions forbidden by the admin. For example, you can prevent the use of Cyrillic or Chinese characters or prevent the use of links by entering appropriate character strings and/or regular expressions.  If the check finds disallowed characters, a message is shown and the user can edit his post and try to save again.',
	'SPSE_PERM_TITLE'							=> 'Permissions:',
	'SPSE_PERMISSION_BEFORE'					=> 'With <b>phpbb´s own authorisation system</b>, you can use the settings for  ',
	'SPSE_USERS_PERMISSIONS'					=> 'User permission ',
	'SPSE_ANDOR'								=> ' and / or ',
	'SPSE_GROUPS_PERMISSIONS'					=> ' group permission  ',
	'SPSE_PERMISSION_AFTER'						=> ' under <b>miscellaneous</b> to set whether the settings for the <b>„%1$s“</b> extension should be applied.',
	'SPSE_SETTINGS_CHARS'						=> 'Settings for disallowed characters/strings',
	'SSPE_NO_LINKS'								=> 'Forbid Links ',
	'SSPE_NO_LINKS_EXPLAIN'						=> 'Here you can select whether it should be possible to write posts that contain Links. If the option <b>"YES"</b> is selected <b>(depending on the set user or group permissions)</b> it will not be possible to send posts with Links.<br><br><b>Note:</b> The user receives a corresponding error message.',
	'SPSE_LANG_KYRILL'							=> 'Forbid Cyrillic characters',
	'SPSE_LANG_KYRILL_EXPLAIN'					=> 'Here you can select whether it should be possible to write posts that contain Cyrillic characters. If the option <b>"YES"</b> is selected, it will not be possible <b>(depending on the set user or group permissions)</b> to send posts with Cyrillic characters.<br><br><b>Note:</b> The user receives a corresponding error message.',
	'SPSE_LANG_CHINESE'							=> 'Forbid Chinese characters',
	'SPSE_LANG_CHINESE_EXPLAIN'					=> 'Here you can select whether it should be possible to write posts that contain Chinese characters. If the option <b>"YES"</b> is selected <b>(depending on the set user or group permissions)</b> it will not be possible to send posts with Chinese characters.<br><br><b>Note:</b> The user receives a corresponding error message.',
	'SPSE_LANG_HINDI'							=> 'Forbid Hindi characters',
	'SPSE_LANG_HINDI_EXPLAIN'					=> 'Here you can select whether it should be possible to write posts that contain Hindi characters. If the option <b>"YES"</b> is selected, it will not be possible <b>(depending on the set user or group permissions)</b> to send posts with Hindi characters.<br><br><b>Note:</b> The user receives a corresponding error message.',
	'SPSE_LANG_CUSTOM'							=> 'Enable custom input',
	'SPSE_LANG_CUSTOM_EXPLAIN'					=> 'Here you can enter individual characters or character strings that are not covered by the above specifications.',
	'SPSE_INVALID_CHARS'						=> 'User-defined input of the characters/strings that are not allowed',
	'SPSE_INVALID_CHARS_EXPLAIN'				=> 'List of all characters that are not allowed. The characters are to be entered separately with a comma and are not case-sensitive.<br><br><b>Example:</b> <b style="color: green">a</b><b style="color: red">,</b><b style="color: green">b</b><b style="color: red">,</b><b style="color: green">c</b><b style="color: red">,</b><b style="color: green">d</b><b style="color: red">,</b><b style="color: green">http://</b> etc.<br><br><b>Attention:</b> The maximum allowed input including commas is <b>255</b> characters',
	'SPSE_INVALID_CHARS_COUNTER_SWITCH'			=> 'Display of the "characters/strings not allowed" in the error message',
	'SPSE_INVALID_CHARS_COUNTER_SWITCH_EXPLAIN'	=> 'Here you can set whether after the message: <b>The message contains the following number of disallowed characters/characters: (number)</b> another line with the following text should be displayed: <b>The following characters/characters are not allowed:</b>, followed by the characters/strings that are not allowed. Then the user is also shown the characters that he has used but is not allowed to use. You can also specify the number of characters/character strings again in the following option.',
	'SPSE_INVALID_CHARS_COUNTER'				=> 'Option for counter of "forbidden characters"',
	'SPSE_INVALID_CHARS_COUNTER_EXPLAIN'		=> 'If the option is activated: <b>Display of "disallowed characters/characters" in the error message</b> can be set here, up to how many disallowed characters/characters found the output of the "unwanted characters" is displayed in the error message should.<br><br>Example: If a value of 5 is set, the output <b>The following characters/characters are not allowed:</b> followed by the characters/characters not allowed is displayed until more than 5 characters/strings that are not allowed can be entered.<br><br>If the <b>Display of "characters/characters/strings that are not allowed" option in the error message</b> option is deactivated, no additional message is output.<br><br >This function is intended to enable users who accidentally used a few disallowed characters/strings to adapt their posting accordingly. If there are a large number of disallowed characters/character strings that can be assumed to be spam, no analysis option should be offered.<br><br><b>Information:</b> According to the default setting, only the characters in the text are displayed to the user that are not allowed.',
	'SSPE_REGEX_INDIVIDUAL_CHECK'				=> 'Custom regex code',
	'SSPE_REGEX_INDIVIDUAL_CHECK_EXPLAIN'		=> 'Here you can activate whether you also want to use individual regex code.',
	'SPSE_SETTINGS_REGEX'						=> 'Settings check by "Regex" coding',
	'SPSE_INVALID_REGEX'						=> 'Enter the "regex" code',
	'SPSE_INVALID_REGEX_EXPLAIN'				=> 'Here you can use a check where you can enter full "regex" code.<br><br>The input format of regular expressions should be treated with particular care. I strongly recommend that you consult the RegEx(s) reference before entering regular expressions.<br><br>Here are some examples if you want to do e.g. links or other checks:<br><br><b><b>Simple example:</b><br></b><b style="color: red">/</b><b style="color: green">http:</b><b style="color: red">|</b><b style="color: green">ftp:</b><b style="color: red">|</b><b style="color: green">www.</b><b style="color: red">/</b><br><br><b>More complex example:</b><br><b style="color: red">/</b><b style="color: green">(((http</b><b style="color: red">|</b><b style="color: green">https):\/\/)</b><b style="color: red">|</b><b style="color: green">(www\.))[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,}(:[0-9]+)?(\/\S*)?</b><b style="color: red">/</b><b style="color: green">i</b>',
	'SPSE_INVALID_REGEX_MSG'					=> 'Note: The entered RegEx expression is invalid and was not accepted!',
	'SPSE_SETTING_SAVED'						=> 'The settings for <b>Spamsecure by 69bruno</b> have been applied successfully.',
	//New1
	'SPSE_SETTINGS_VARIANT'						=> 'Settings of the verification variant',
	'SPSE_SEARCH_COMPLETE'						=> 'Verification variant',
	'SPSE_SEARCH_COMPLETE_EXPLAIN'				=> 'Here you can select which fields in the input mask should be searched. You can choose between the following options: <br><br><b>Check all existing input fields</b><br>&bull; Here, all fields in the input window where individual character input is possible are checked for prohibited characters.<br><br><b>Only check the input in the message field</b><br>&bull; Here, only the input in the notification text field is checked for individual prohibited characters.<br><br><b>Default: </b>Only check the input in the message field',
	'SPSE_ALL'									=> 'Check all existing input fields',
	'SPSE_ONLY_MESSAGE'							=> 'Only check the message field input',
]);
