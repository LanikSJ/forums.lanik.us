<?php

/**
 * @author    MarkusWME <markuswme@pcgamingfreaks.at>
 * @copyright 2017 MarkusWME
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 */

namespace pcgf\ajaxregistrationcheck\controller;

use phpbb\db\driver\factory;
use phpbb\event\dispatcher;
use phpbb\json_response;
use phpbb\request\request;
use phpbb\user;

/** @version 1.0.0 */
class controller
{
    /** @var request Request object */
    protected $request;

    /** @var factory Database factory object */
    protected $db_factory;

    /** @var user User object */
    protected $user;

    /** @var dispatcher phpBB event dispatcher */
    protected $dispatcher;

    /**
     * Controller constructor
     *
     * @access public
     * @since  1.0.0
     *
     * @param request    $request    Request object
     * @param factory    $db_factory Database factory object
     * @param user       $user       User object
     * @param dispatcher $dispatcher phpBB event dispatcher
     */
    public function __construct(request $request, factory $db_factory, user $user, dispatcher $dispatcher)
    {
        $this->request = $request;
        $this->db_factory = $db_factory;
        $this->user = $user;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Function that checks the user input
     *
     * @access public
     * @since  1.0.0
     *
     * @param string $type The input type
     */
    public function check($type)
    {
        // Load needed language data
        $this->user->add_lang('ucp');
        $this->user->add_lang_ext('pcgf/ajaxregistrationcheck', array('ajaxregistrationcheck'));
        $response = new json_response();
        $response_text = array('INVALID QUERY', $this->user->lang('PCGF_AJAXREGISTRATIONCHECK_INVALID_QUERY'));
        if ($this->request->is_ajax())
        {
            if ($type === 'username')
            {
                $response_text = $this->check_username($this->request->variable('search', ''));
            }
            else if ($type === 'email')
            {
                $response_text = $this->check_email($this->request->variable('search', ''));
            }
        }
        $response->send($response_text);
    }
    /**
     * Check whether a username is taken or disallowed by the board admin
     *
     * @access private
     * @since  1.1.1
     *
     * @param string $username The username to check
     *
     * @return array Array with the status and the response message
     */
    private function check_username($username)
    {
        if ($username === '')
        {
            return array('INVALID QUERY', $this->user->lang('PCGF_AJAXREGISTRATIONCHECK_INVALID_QUERY'));
        }
        // Check if the name is already used (compare the cleaned name like phpBB core does)
        $clean_username = utf8_clean_string($username);
        $query = 'SELECT username
                    FROM ' . USERS_TABLE . "
                    WHERE username_clean = '" . $this->db_factory->sql_escape($clean_username) . "'";
        $result = $this->db_factory->sql_query($query);
        $row = $this->db_factory->sql_fetchrow($result);
        $this->db_factory->sql_freeresult($result);
        if ($row)
        {
            return array('NOT OK', $this->user->lang('USERNAME_TAKEN_USERNAME'));
        }
        // Check if the username is disallowed by the board admin
        $query = 'SELECT disallow_username
                    FROM ' . DISALLOW_TABLE;
        $result = $this->db_factory->sql_query($query);
        while ($disallowed_user = $this->db_factory->sql_fetchrow($result))
        {
            // Check if the username matches the rule (wildcards: % matches anything)
            $pattern = '/^' . str_replace('%', '.*?', preg_quote($disallowed_user['disallow_username'], '#')) . '$/i';
            if (preg_match($pattern, $clean_username))
            {
                $this->db_factory->sql_freeresult($result);
                return array('NOT OK', $this->user->lang('USERNAME_DISALLOWED_USERNAME'));
            }
        }
        $this->db_factory->sql_freeresult($result);
        return array('OK', $this->user->lang('PCGF_AJAXREGISTRATIONCHECK_USERNAME_OK'));
    }
    /**
     * Check whether an email address is taken or banned by the board admin
     *
     * @access private
     * @since  1.1.1
     *
     * @param string $email The email address to check
     *
     * @return array Array with the status and the response message
     */
    private function check_email($email)
    {
        if ($email === '')
        {
            return array('INVALID QUERY', $this->user->lang('PCGF_AJAXREGISTRATIONCHECK_INVALID_QUERY'));
        }
        // Check if the email is already used
        $query = 'SELECT user_email
                    FROM ' . USERS_TABLE . "
                    WHERE user_email = '" . $this->db_factory->sql_escape($email) . "'";
        $result = $this->db_factory->sql_query($query);
        $row = $this->db_factory->sql_fetchrow($result);
        $this->db_factory->sql_freeresult($result);
        if ($row)
        {
            return array('NOT OK', $this->user->lang('EMAIL_TAKEN_EMAIL'));
        }
        // Check if the email is banned by the board admin
        $query = 'SELECT ban_email
                    FROM ' . BANLIST_TABLE . "
                    WHERE ban_email = '" . $this->db_factory->sql_escape($email) . "'";
        $result = $this->db_factory->sql_query($query);
        while ($banned_email = $this->db_factory->sql_fetchrow($result))
        {
            // Check if the email matches the rule (wildcards: % matches anything)
            $pattern = '/^' . str_replace('%', '.*?', preg_quote($banned_email['ban_email'], '#')) . '$/i';
            if (preg_match($pattern, $email))
            {
                $this->db_factory->sql_freeresult($result);
                return array('NOT OK', $this->user->lang('EMAIL_BANNED_EMAIL'));
            }
        }
        $this->db_factory->sql_freeresult($result);
        return array('OK', $this->user->lang('PCGF_AJAXREGISTRATIONCHECK_EMAIL_OK'));
    }
}