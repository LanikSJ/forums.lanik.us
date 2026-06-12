<?php
/** 
*
* @package acp
* @version $Id: acp_inactive_users.php,v 2.0 2007/08/23
* @copyright (c) 2007 Waleed Zuberi / Double U Designs [http://www.doubleudesigns.com/]
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @package module_install
*/
class acp_inactive_users_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_inactive_users',
			'title'		=> 'INACTIVE_USERS_LIST',
			'version'	=> '2.0',
			'modes'		=> array(
				'list'		=> array('title' => 'INACTIVE_USERS_LIST', 'auth' => 'acl_a_user', 'cat' => 'INACTIVE_USERS_LIST'),
			),
		);
	}

	function install()
	{
	}

	function uninstall()
	{
	}
}

?>
