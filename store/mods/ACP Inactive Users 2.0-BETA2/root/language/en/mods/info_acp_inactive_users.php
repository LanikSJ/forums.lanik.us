<?php
/**
*
* acp_inactive_users [English]
*
* @package language
* @version $Id: acp_inactive_users.php,v 2.0 2007/09/07 20:54:24
* @copyright (c) 2007 Waleed Zuberi / Double U Designs [http://www.doubleudesigns.com/]
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

// Inactive Users MOD 2.0
$lang = array_merge($lang, array(
	'INACTIVE_USERS_LIST'			=> 'Inactive Users MOD',
	'INACTIVE_USERS_LIST_EXPLAIN'	=> '<p>From this module you may choose to deactivate or delete the accounts of members who have not logged on to the forum for some time. The time of <strong>Last Visit</strong> is shown relative to the current board time. Note that this will be ’rounded off’ to a certain degree.</p> <p>Most inactive users with the least number of posts are shown first, but you can sort the list according to the options given below. The current account status (for example <em>registered</em>, <em>deactivated</em>) of each user can be seen in the <strong>Status</strong> column. Please note that board founders are not shown for security reasons.</p> <p>You will be asked to confirm the deletion of any users.</p>',
	'USER_DELETE_DONE'	=> 'User(s) deleted successfully',
	'USER_DEACTIVATED_DONE'	=> 'User(s) deactivated successfully',
	'USER_DEACTIVATED_ALREADY'	=> 'One or more of the selected users is already deactivated. Please go back and re-submit the form.',
	'YESTERDAY'	=> 'Yesterday',
	'TODAY'	=> 'Today',
	'INACTIVE_DELETE_CONFIRM'	=> 'Are you sure you wish to delete the selected user(s)? Please note that this action is final, and <strong>cannot be undone.</strong>',
	'YEAR'	=> ' year',
	'YEARS'	=> ' years',
	'MONTH'	=> ' month',
	'MONTHS'	=> ' months',
	'DAY'	=> ' day',
	'DAYS'	=> ' days',
	'HOUR'	=> ' hour',
	'HOURS'	=> ' hours',
	'MIN'	=> ' minute',
	'MINS'	=> ' minutes',
	'SECS'	=> ' seconds',
	'SEC'	=> ' second',
	'AGO'	=> ' ago',
	'ONLINE_NOW'	=> 'Online now.',
	'NEVER_VISIT'	=> 'Never',
	'USERNAME'	=> 'Username',
	'ACC_STATUS'	=> 'Status',
	'POSTS'	=> 'Posts',
	'REG_DATE'	=> 'Registered date',
	'LAST_VISIT'	=> 'Last visit',
	'MARK'	=> 'Mark',
	'SORT_DEFAULT'	=> 'Most inactive, least posts',
	'SORT_ACC_STATUS'	=> 'Account status',
	'SORT_POSTS'	=> 'Posts',
	'SHOW_ONLY'	=> 'Show only users who visited in last:',
	'CANNOT_DELETE_FOUNDER'	=> 'You are not allowed to delete founder accounts.',
));
?>
