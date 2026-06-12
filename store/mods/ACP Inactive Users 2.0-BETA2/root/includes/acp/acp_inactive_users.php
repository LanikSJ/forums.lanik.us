<?php
/** 
*
* View how long it has been since your members' last visit
* By Waleed Zuberi, Double U Designs [http://www.doubleudesigns.com/]
*
* @package acp
* @version $Id: acp_inactive_users.php,v 2.0.2 2007/10/18 22:34:23 Waleed Exp $
* @copyright (c) 2007 Double U Designs
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
* @todo feature - individual user ?search.
*/

/**
* @package acp
*/
class acp_inactive_users
{
	var $u_action;
	var $new_config;

	function main($id, $mode)
	{
		global $db, $user, $auth, $template;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		$error = $notify = array();

		$user->add_lang(array('mods/info_acp_inactive_users', 'acp/users'));

		$action = request_var('action', '');
		$mark	= (isset($_REQUEST['mark'])) ? request_var('mark', array(0)) : array();
        $start = request_var('start', 0);
		
        $limit = request_var('st', 'a');
		$sort_key	= request_var('sk', 'l');
		$sort_dir	= request_var('sd', 'a');

		if (sizeof($mark))
		{
			include($phpbb_root_path . 'includes/functions_user.' . $phpEx);

			$sql = 'SELECT username 
				FROM ' . USERS_TABLE . '
				WHERE ' . $db->sql_in_set('user_id', $mark);
			$result = $db->sql_query($sql);

			$user_affected = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$user_affected[] = $row['username'];
			}
			$db->sql_freeresult($result);

			switch ($action)
			{
				case 'deactivate':

				// if the admin using this is not a board founder, then s/he can only look at the list 
				if ($user->data['user_type'] != USER_FOUNDER)
				{
					trigger_error($user->lang['NO_AUTH_OPERATION'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

				foreach ($mark as $user_id)
				{		
					// user cannot deactivate own account
					if ($user->data['user_id'] == $user_id)
					{
						trigger_error($user->lang['CANNOT_DEACTIVATE_YOURSELF'] . adm_back_link($this->u_action), E_USER_WARNING);
					}

					// to be extra safe, trigger error if user being deleted is a founder
					$sql = 'SELECT user_type FROM ' . USERS_TABLE . ' WHERE user_id=' . $user_id . '';
					$result = $db->sql_query($sql);
					$row = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);

					// if marked user is already deactivated, going through with the process will activate them
					// so we stop the script and trigger an error ;)
					if ($row['user_type'] == USER_INACTIVE)
					{
						trigger_error($user->lang['USER_DEACTIVATED_ALREADY'] . adm_back_link($this->u_action));
					}
					if ($row['user_type'] == USER_FOUNDER)
					{
						trigger_error($user->lang['CANNOT_DEACTIVATE_FOUNDER'] . adm_back_link($this->u_action), E_USER_WARNING);
					}

					// if everything checks out, we deactivate the user 							
					if ($user->data['user_type'] == USER_FOUNDER && $user->data['user_id'] != $user_id)
					{
						user_active_flip('deactive', $user_id);
					}
				}

				add_log('admin', 'LOG_USER_INACTIVE', implode(', ', $user_affected));
				trigger_error($user->lang['USER_DEACTIVATED_DONE'] . adm_back_link($this->u_action));

			break;

			case 'delete':

				// if the admin using this is not a board founder, then s/he can only look at the list
				if ($user->data['user_type'] != USER_FOUNDER)
				{
					trigger_error($user->lang['NO_AUTH_OPERATION'] . adm_back_link($this->u_action), E_USER_WARNING);
				}
				
				// not authorized to delete users
				if (!$auth->acl_get('a_userdel'))
				{
					trigger_error($user->lang['NO_AUTH_OPERATION'] . adm_back_link($this->u_action), E_USER_WARNING);
				}
				
				// ask user to confirm sensitive operation
				if (confirm_box(true))
				{
					foreach ($mark as $user_id)
					{
						// to be extra safe, trigger error if user being deleted is a founder
						$sql = 'SELECT user_type FROM ' . USERS_TABLE . ' WHERE user_id=' . $user_id . '';
						$result = $db->sql_query($sql);
						$row = $db->sql_fetchrow($result);
						$db->sql_freeresult($result);
						
						if ($row['user_type'] == USER_FOUNDER)
						{
							trigger_error($user->lang['CANNOT_DELETE_FOUNDER'] . adm_back_link($this->u_action), E_USER_WARNING);
						}
						
						// user cannot delete own account
						if ($user->data['user_id'] == $user_id)
						{
							trigger_error($user->lang['CANNOT_REMOVE_YOURSELF'] . adm_back_link($this->u_action), E_USER_WARNING);
						}
						
						// all set, do action
						if ($user->data['user_type'] == USER_FOUNDER && $user->data['user_id'] != $user_id)
						{
							$sql = "SELECT username FROM ". USERS_TABLE ." WHERE user_id =". $user_id;
							$result = $db->sql_query($sql);
							$processed_user = $db->sql_fetchrow($result);
							$db->sql_freeresult($result);
	
							// retain posts, user id, username of person being deleted
							user_delete('retain', $user_id, $processed_user['username']);
						}
					}
					
					add_log('admin', 'LOG_USER_DELETED', implode(', ', $user_affected));
					trigger_error($user->lang['USER_DELETE_DONE'] . adm_back_link($this->u_action));
				}
				
				confirm_box(false, $user->lang['INACTIVE_DELETE_CONFIRM'], build_hidden_fields(array('action' => 'delete', 'mark' => $mark)));
				
			break;
			}
		}
		
        // How long has it been since the last visit?
        // function from WP_TIME_SINCE [http://r09.co.uk/wp_time_since/] plugin (by Rupert Bedford)
        // and then modified to fit!
		function time_compare($last_visit_time = 0)
		{
			global $user;
			
			// current time minus lastvisit time to get difference in seconds
			$time = time();
			$diff = $time - $last_visit_time;
			
			// less than seconds
			if ($diff < 60)
			{
				if ($diff == 0 or $diff > 1)
				{
					return $diff . $user->lang['SECS'] . $user->lang['AGO'];
				}
				else
				{
					return $diff . $user->lang['SEC'] . $user->lang['AGO'];
				}
			}
			
			// less than minutes
			else if ($diff < 3600)
			{
				$minutes = $diff / 60;
				$minutes = explode(".", $minutes);
				$minutes = $minutes[0];
				
				$seconds = $diff % 60;

				if ($minutes == 1)
				{
					if ($seconds == 1)
					{
						return $minutes . $user->lang['MIN'] . $user->lang['COMMA_SEPARATOR'] . $seconds . $user->lang['SEC'] . $user->lang['AGO'];
					}
					else if ($seconds > 1 or $seconds == 0)
					{
						return $minutes . $user->lang['MIN'] . $user->lang['COMMA_SEPARATOR'] . $seconds . $user->lang['SECS'] . $user->lang['AGO'];
					}
				}
				else if ($minutes > 1)
				{
					if ($seconds == 1)
					{
						return $minutes . $user->lang['MINS'] . $user->lang['COMMA_SEPARATOR'] . $seconds . $user->lang['SEC'] . $user->lang['AGO'];
					}
					else if ($seconds > 1 or $seconds == 0)
					{
						return $minutes . $user->lang['MINS'] . $user->lang['COMMA_SEPARATOR'] . $seconds . $user->lang['SECS'] . $user->lang['AGO'];
					}
				}
			}
					
			// days ago
			else if ($diff < 86400)
			{
				$hours = $diff / 3600;
				$hours = explode(".", $hours);
				$hours = $hours[0];

				$minutes = ($diff % 3600) / 60;
				$minutes = explode(".", $minutes);
				$minutes = $minutes[0];

				if ($hours == 1)
				{
					if ($minutes == 1)
					{
						return $hours . $user->lang['HOUR'] . $user->lang['COMMA_SEPARATOR'] . $minutes . $user->lang['MIN'] . $user->lang['AGO'];
					}
					else if ($minutes > 1 or $minutes == 0)
					{
								return $hours . $user->lang['HOUR'] . $user->lang['COMMA_SEPARATOR'] . $minutes . $user->lang['MINS'] . $user->lang['AGO'];
					}
				}
				else if ($hours > 1)
				{
					if ($minutes == 1)
					{
						return $hours . $user->lang['HOURS'] . $user->lang['COMMA_SEPARATOR'] . $minutes . $user->lang['MIN'] . $user->lang['AGO'];
					}
					else if ($minutes > 1 or $minutes == 0)
					{
						return $hours . $user->lang['HOURS'] . $user->lang['COMMA_SEPARATOR'] . $minutes . $user->lang['MINS'] . $user->lang['AGO'];
					}
				}
			}
			else if ($diff < 604800)
			{
				$days = $diff / 86400;
				$days = explode(".", $days);
				$days = $days[0];
						
				$hours = ($diff % 86400) / 3600;
				$hours = explode(".", $hours);
				$hours = $hours[0];
						
				if ($days == 1)
				{
					if ($hours == 1)
					{
						return $days . $user->lang['DAY'] . $user->lang['COMMA_SEPARATOR'] . $hours . $user->lang['HOUR'] . $user->lang['AGO'];
					}
					else if ($hours > 1 or $hours == 0)
					{
						return $days . $user->lang['DAY'] . $user->lang['COMMA_SEPARATOR'] . $hours . $user->lang['HOURS'] . $user->lang['AGO'];
					}
				}
				else if ($days > 1)
				{
					if ($hours == 1)
					{
						return $days . $user->lang['DAYS'] . $user->lang['COMMA_SEPARATOR'] . $hours . $user->lang['HOUR'] . $user->lang['AGO'];
					}
					else if ($hours > 1 or $hours == 0)
					{
						return $days . $user->lang['DAYS'] . $user->lang['COMMA_SEPARATOR'] . $hours . $user->lang['HOURS'] . $user->lang['AGO'];
					}
				}
			}
			else if ($diff < 2592000)
			{
				$days = $diff / 86400;
				$days = explode(".", $days);
				$days = $days[0];
				
				$hours = ($diff % 86400) / 3600;
				$hours = explode(".", $hours);
				$hours = $hours[0];

				if ($days == 1)
				{
					return $days . $user->lang['DAY'] . $user->lang['AGO'];
				}
				else if ($days > 1)
				{
					return $days . $user->lang['DAYS'] . $user->lang['AGO'];
				}
			}
			else if ($diff < 31536000)
			{
				$months = $diff / 2592000;
				$months = explode(".", $months);
				$months = $months[0];

				$days = ($diff % 2592000) / 86400;
				$days = explode(".", $days);
				$days = $days[0];
						
				if ($months == 1)
				{
					if ($days == 1)
					{
						return $months . $user->lang['MONTH'] . $user->lang['COMMA_SEPARATOR'] . $days . $user->lang['DAY'] . $user->lang['AGO'];
					}
					else if ($days > 1 or $days == 0)
					{
						return $months . $user->lang['MONTH'] . $user->lang['COMMA_SEPARATOR'] . $days . $user->lang['DAYS'] . $user->lang['AGO'];
					}
				}
				else if ($months > 1)
				{
					if ($days == 1)
					{
						return $months . $user->lang['MONTHS'] . $user->lang['COMMA_SEPARATOR'] . $days . $user->lang['DAY'] . $user->lang['AGO'];
					}
					else if ($days > 1 or $days == 0)
					{
						return $months . $user->lang['MONTHS'] . $user->lang['COMMA_SEPARATOR'] . $days . $user->lang['DAYS'] . $user->lang['AGO'];
					}
				}
			}
			else if ($diff >= 31536000)
			{
				$years = $diff / 31536000;
				$years = explode(".", $years);
				$years = $years[0];

				$months = ($diff % 31536000) / 2592000;
				$months = explode(".", $months);
				$months = $months[0];

				if ($years == 1)
				{
					if ($months == 1)
					{
						return $years . $user->lang['YEAR'] . $user->lang['COMMA_SEPARATOR'] . $months . $user->lang['MONTH'] . $user->lang['AGO'];
					}
					else if ($months > 1 or $months == 0)
					{
						return $years . $user->lang['YEAR'] . $user->lang['COMMA_SEPARATOR'] . $months . $user->lang['MONTHS'] . $user->lang['AGO'];
					}
				}
				else if ($years > 1)
				{
					if ($months == 1)
					{
						return $years . $user->lang['YEARS'] . $user->lang['COMMA_SEPARATOR'] . $months . $user->lang['MONTH'] . $user->lang['AGO'];
					}
						else if ($months > 1 or $months == 0)
						{
							return $years . $user->lang['YEARS'] . $user->lang['COMMA_SEPARATOR'] . $months . $user->lang['MONTHS'] . $user->lang['AGO'];
						}
				}
			}
		}
      
		function is_online ($user_id)
		{
			global $user, $config, $db;

			if ($config['load_onlinetrack'])
			{
				$sql = 'SELECT MAX(session_time) AS session_time, MIN(session_viewonline) AS session_viewonline
					FROM ' . SESSIONS_TABLE . "
					WHERE session_user_id = " . $user_id;
				$result = $db->sql_query($sql);
				$online = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);
			
				$online['session_time'] = (isset($online['session_time'])) ? $online['session_time'] : 0;
				$online['session_viewonline'] = (isset($online['session_viewonline'])) ? $online['session_viewonline'] : 0;

				$update_time = $config['load_online_time'] * 60;
				return $online = (time() - $update_time < $online['session_time'] && ((isset($online['session_viewonline']) && $online['session_viewonline']) || $auth->acl_get('u_viewonline'))) ? true : false;
			}
			else
			{
				$online = false;
			}
		}

        $this->tpl_name = 'acp_inactive_users';
        $this->page_title = 'INACTIVE_USERS_LIST';

        // pagination stuff...
		$limit_days = array('a' => $user->lang['ALL_ENTRIES'], 'n' => $user->lang['NEVER_VISIT'], 1 => $user->lang['1_MONTH'], 2 => $user->lang['6_MONTHS'], 3 => $user->lang['1_YEAR']);
		$limit_days_sql = array('a' => '', 'n' => 'user_lastvisit = 0 AND ', 1 => 'user_lastvisit >= (' . time() . '- ' . (30 * 86400) . ') AND ', 2 => 'user_lastvisit >= (' . time() . '- ' . (180 * 86400) . ') AND ', 3 => 'user_lastvisit >= (' . time() . '- ' . (365 * 86400) . ') AND ');

		$sort_by_text = array('d' => $user->lang['SORT_DEFAULT'], 's' => $user->lang['SORT_ACC_STATUS'], 'j' => $user->lang['SORT_REG_DATE'], 'l' => $user->lang['SORT_LAST_VISIT'], 'p' => $user->lang['SORT_POSTS'], 'u' => $user->lang['SORT_USERNAME']);
		$sort_by_sql = array('d' => 'user_lastvisit, user_posts', 's' => 'user_inactive_time',  'j' => 'user_regdate', 'l' => 'user_lastvisit', 'p' => 'user_posts', 'u' => 'username_clean');

		$s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';
		gen_sort_selects($limit_days, $sort_by_text, $limit, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);

		$sql_where = $limit_days_sql[$limit];
		$sql_sort = $sort_by_sql[$sort_key] . ' ' . (($sort_dir == 'd') ? 'DESC' : 'ASC');

		$sql = 'SELECT *
			FROM ' . USERS_TABLE . ' 
			WHERE ' . $sql_where . ' user_type !=2 AND user_type !=' . USER_FOUNDER . '
			ORDER BY ' . $sql_sort;
		$result = $db->sql_query_limit($sql, $config['topics_per_page'], $start);
						
        while ($row = $db->sql_fetchrow($result))
        {		
			$template->assign_block_vars('users', array(
				'ADMIN_URL' => append_sid("{$phpbb_admin_path}index.$phpEx", "i=users&amp;mode=overview&amp;u={$row['user_id']}"),
				'USERNAME'	=> $row['username'],
				'USER_COLOR'	=> $row['user_colour'],
				'USER_TYPE'		=> $row['user_type'],
				'ACTIVATE_LINK'	=> append_sid("{$phpbb_admin_path}index.$phpEx", 'i=inactive&amp;mode=list', $user->session_id),
				'POSTS'		=> $row['user_posts'],
				'USER_ID' => $row['user_id'],
				'REG_DATE' => $user->format_date($row['user_regdate'], $user->data['user_dateformat']),
				'NEVER_VISIT'	=> (!$row['user_lastvisit']) ? true : false,
				'LAST_VISIT'	=> time_compare($row['user_lastvisit']),
				'S_ONLINE'		=> ($config['load_onlinetrack'] && is_online($row['user_id'])) ? true : false,
			));
		}
		
		/* this count comes after the above vars are assigned to $template so they don't interfere with each other */
		// if the list is being sorted, count the results
		if ($sql_where)
		{
			$sql = 'SELECT COUNT(user_id) AS total_users
				FROM ' . USERS_TABLE . '
				WHERE ' . $sql_where . ' user_type !=2 AND user_type !=' . USER_FOUNDER .'
				ORDER BY ' . $sql_sort;
			$result = $db->sql_query($sql);
			$total_users = (int) $db->sql_fetchfield('total_users');
			$db->sql_freeresult($result);
		}
		// otherwise, take the total from the DB
		else
		{
			$total_users = $config['num_users'];
		}
		
        $l_total_user_s = ($total_users == 0) ? 'TOTAL_USERS_ZERO' : 'TOTAL_USERS_OTHER';
		
        $template->assign_vars(array(
            'PAGINATION'	=> generate_pagination($this->u_action . "&amp;$u_sort_param", $total_users, $config['topics_per_page'], $start, true),
            'PAGE_NUMBER'	=> on_page($total_users, $config['topics_per_page'], $start),
            'TOTAL_USERS'	=> sprintf($user->lang[$l_total_user_s], $total_users),
            'U_ACTION'		=> $this->u_action,
        ));

		$option_ary = array('delete' => 'DELETE', 'deactivate' => 'DEACTIVATE');
				
        $template->assign_vars(array(
            'S_INACTIVE_OPTIONS'	=> build_select($option_ary),
            'S_SORT_KEY'	=> $s_sort_key,
			'S_SORT_DIR'	=> $s_sort_dir,
			'S_LIMIT_DAYS'	=> $s_limit_days,
        ));

        $db->sql_freeresult($result);
	
	}
}

?>