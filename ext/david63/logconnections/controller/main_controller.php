<?php
/**
 *
 * @package Log Connections
 * @copyright (c) 2017 david63
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace david63\logconnections\controller;

use phpbb\config\config;
use phpbb\log\log;
use phpbb\user;
use phpbb\auth\auth;
use phpbb\language\language;

/**
 * Main controller
 */
class main_controller
{
	/** @var config */
	protected $config;

	/** @var log */
	protected $log;

	/** @var user */
	protected $user;

	/** @var auth */
	protected $auth;

	/** @var language */
	protected $language;

	/**
	 * Constructor for listener
	 *
	 * @param config     		$config     Config object
	 * @param log        		$log        phpBB log
	 * @param user       		$user       User object
	 * @param auth       		$auth       Auth object
	 * @param language   		$language   Language object
	 *
	 * @access public
	 */
	public function __construct(config $config, log $log, user $user, auth $auth, language $language)
	{
		$this->config   = $config;
		$this->log      = $log;
		$this->user     = $user;
		$this->auth     = $auth;
		$this->language = $language;
	}

	/**
	 * Controller for logconnections
	 *
	 * @param string     $name
	 * @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
	 */

	/**
	 * Log the new user
	 *
	 * @param object $event The event object
	 * @return null
	 * @access public
	 */
	public function log_new_user($event)
	{
		$new_user_row = $event['user_row'];

		$additional_data['reportee_id']	= $event['user_id'];
		$additional_data[]				= $new_user_row['user_email'];
		$additional_data[]				= $this->user->data['session_browser'];

		$user_ip = (array_key_exists('user_ip', $new_user_row)) ? $new_user_row['user_ip'] : '0.0.0.0';

		$this->log->add('user', $event['user_id'], $user_ip, 'LOG_NEW_USER_CREATED', time(), $additional_data);
	}

	/**
	 * Log the successful connection
	 *
	 * @param object $event The event object
	 * @return null
	 * @access public
	 */
	public function login_connect($event)
	{
		// We don't need to log Admin logins as they are already logged
		$user_type = ($this->auth->acl_raw_data($this->user->data['user_id'], 'a_')) ? 'admin' : 'user';

		if ($user_type == 'user')
		{
			$additional_data['reportee_id']	= $this->user->data['user_id'];
			$log_message					= ($this->user->data['session_autologin']) ? 'LOG_AUTO_LOGIN' : 'LOG_SUCCESSFUL';

			if ($this->config['log_browser'])
			{

				$additional_data[] = $this->user->data['session_browser'];
				$log_message       = ($this->user->data['session_autologin']) ? 'LOG_AUTO_LOGIN_BROWSER' : 'LOG_SUCCESSFUL_BROWSER';
			}

			$this->log->add('user', $this->user->data['user_id'], $this->user->ip, $log_message, time(), $additional_data);
		}
	}

	/**
	 * Log failed login attempts
	 *
	 * @param object $event The event object
	 * @return null
	 * @access public
	 */
	public function failed_login($event)
	{
		$error_msg	= $event['err'];
		$result    	= $event['result'];
		$user_row	= $result['user_row'];
		$username  	= $event['username'];
		$user_id	= $user_row['user_id'];
		$user_ip	= (!empty($user_row['user_ip'])) ? $user_row['user_ip'] : $this->user->ip;

		$additional_data   = [];
		$additional_data['reportee_id'] = (int) $user_id;

		// We want to log Admin fails to the Admin log and User fails to the user log
		$log_type = ($this->auth->acl_raw_data($user_id, 'a_')) ? 'admin' : 'user';

		switch ($result['status'])
		{
			case LOGIN_ERROR_USERNAME:
				$error_msg         = 'ERROR_LOGIN_USERNAME';
				$log_type          = 'user'; // This can only be user as we have no data to test
				$additional_data[] = $username;
				break;

			case LOGIN_ERROR_PASSWORD:
				if (!array_key_exists('user_password', $user_row))
				{
					$error_msg = 'NO_PASSWORD_ENTERED';
				}
				else
				{
					$error_msg = 'ERROR_LOGIN_PASSWORD';
				}
				break;

			case LOGIN_ERROR_ACTIVE:
				$error_msg = 'ERROR_LOGIN_ACTIVE';
				break;

			case LOGIN_ERROR_ATTEMPTS:
				$error_msg = 'ERROR_LOGIN_ATTEMPTS';
				break;

			case LOGIN_ERROR_PASSWORD_CONVERT:
				$error_msg = 'ERROR_LOGIN_PASSWORD_CONVERT';
				break;

			case LOGIN_BREAK:
				$error_msg = 'ERROR_LOGIN_BREAK';
				break;

			default: // Let's have a catchall for any other failed logins
				$log_type = 'user';
				if ($result['status'] != '')
				{
					$additional_data[] = $result['status'];
					$error_msg         = 'ERROR_LOGIN_UNKNOWN';
				}
				else
				{
					$error_msg = 'UKNOWN_STATUS_ERROR';
				}
				$additional_data[] = $username;
				break;
		}

		$this->log->add($log_type, $user_id, $user_ip, $error_msg, time(), $additional_data);
	}

	/**
	 * Log the user logging out
	 *
	 * @param object $event The event object
	 * @return null
	 * @access public
	 */
	public function log_logout($event)
	{
		$user_id                        = $event['user_id'];
		$session_id                     = $event['session_id'];
		$additional_data['reportee_id'] = $user_id;

		// No need to log ANONYMOUS logouts
		if ($this->user->ip && $user_id != ANONYMOUS)
		{
			$this->log->add('user', $user_id, $this->user->ip, 'LOG_USER_LOGOUT', time(), $additional_data);
		}
	}
}
