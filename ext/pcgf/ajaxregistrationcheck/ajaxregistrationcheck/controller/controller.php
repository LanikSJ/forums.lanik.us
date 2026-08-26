<?php
/**
 * @author MarkusWME <markuswme@pcgamingfreaks.at>
 * @copyright 2017 MarkusWME
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 */
namespace pcgf\ajaxregistrationcheck\controller;

use phpbb\db\driver\factory;
use phpbb\json_response;
use phpbb\request\request;
use phpbb\user;

/** @version 1.0.1 (atualizado para phpBB 3.3.15) */
class controller
{
    /** @var request $request Request object */
    protected $request;

    /** @var factory $db Database object */
    protected $db;

    /** @var user $user User object */
    protected $user;

    /**
     * Controller constructor
     *
     * @param request $request Request object
     * @param factory $db Database object
     * @param user $user User object
     */
    public function __construct(request $request, factory $db, user $user)
    {
        $this->request = $request;
        $this->db = $db;
        $this->user = $user;
    }

    /**
     * Function that checks the user input
     *
     * @param string $type The input type
     */
    public function check($type)
    {
        $this->user->add_lang('ucp');
        $this->user->add_lang_ext('pcgf/ajaxregistrationcheck', array('ajaxregistrationcheck'));

        $response = new json_response();
        $response_text = array('INVALID QUERY', $this->user->lang('PCGF_AJAXREGISTRATIONCHECK_INVALID_QUERY'));

        if (!$this->request->is_ajax() || strtoupper($this->request->server('REQUEST_METHOD')) !== 'POST')
        {
            $response->send($response_text);
            return;
        }

        switch ($type)
            {
                case 'username':
                    $username = $this->request->variable('search', '', true);

                    if ($username !== '')
                    {
                        // Check if the name is already used
                        $query = 'SELECT user_id
                                    FROM ' . USERS_TABLE . "
                                    WHERE username_clean = '" . $this->db->sql_escape(utf8_clean_string($username)) . "'";
                        $result = $this->db->sql_query_limit($query, 1);

                        if ($this->db->sql_fetchrow($result))
                        {
                            $response_text[0] = 'NOT OK';
                            $response_text[1] = $this->user->lang('USERNAME_TAKEN_USERNAME');
                        }
                        $this->db->sql_freeresult($result);

                        if ($response_text[0] !== 'NOT OK')
                        {
                            $query = 'SELECT disallow_username
                                        FROM ' . DISALLOW_TABLE;
                            $result = $this->db->sql_query($query);

                            while ($disallowed_user = $this->db->sql_fetchrow($result))
                            {
                                $pattern = str_replace('\*', '.*', preg_quote($disallowed_user['disallow_username'], '/'));

                                if (preg_match('/^' . $pattern . '$/i', $username))
                                {
                                    $response_text[0] = 'NOT OK';
                                    $response_text[1] = $this->user->lang('USERNAME_DISALLOWED_USERNAME');
                                    break;
                                }
                            }
                            $this->db->sql_freeresult($result);

                            if ($response_text[0] !== 'NOT OK')
                            {
                                $response_text[0] = 'OK';
                                $response_text[1] = $this->user->lang('PCGF_AJAXREGISTRATIONCHECK_USERNAME_OK');
                            }
                        }
                    }
                break;

                case 'email':
                    $email = $this->request->variable('search', '', true);

                    if ($email !== '')
                    {
                        if (!preg_match('/^' . get_preg_expression('email') . '$/i', $email))
                        {
                            $response_text[0] = 'NOT OK';
                            $response_text[1] = $this->user->lang('PCGF_AJAXREGISTRATIONCHECK_EMAIL_INVALID');
                        }

                        if ($response_text[0] !== 'NOT OK')
                        {
                            // Check if the email is already used
                            $query = 'SELECT user_id
                                        FROM ' . USERS_TABLE . "
                                        WHERE user_email = '" . $this->db->sql_escape($email) . "'";
                            $result = $this->db->sql_query_limit($query, 1);

                            if ($this->db->sql_fetchrow($result))
                            {
                                $response_text[0] = 'NOT OK';
                                $response_text[1] = $this->user->lang('EMAIL_TAKEN_EMAIL');
                            }
                            $this->db->sql_freeresult($result);
                        }

                        if ($response_text[0] !== 'NOT OK')
                        {
                            $query = 'SELECT ban_email
                                        FROM ' . BANLIST_TABLE . "
                                        WHERE ban_email <> ''";
                            $result = $this->db->sql_query($query);

                            while ($banned_email = $this->db->sql_fetchrow($result))
                            {
                                $pattern = str_replace('\*', '.*', preg_quote($banned_email['ban_email'], '/'));

                                if (preg_match('/^' . $pattern . '$/i', $email))
                                {
                                    $response_text[0] = 'NOT OK';
                                    $response_text[1] = $this->user->lang('EMAIL_BANNED_EMAIL');
                                    break;
                                }
                            }
                            $this->db->sql_freeresult($result);

                            if ($response_text[0] !== 'NOT OK')
                            {
                                $response_text[0] = 'OK';
                                $response_text[1] = $this->user->lang('PCGF_AJAXREGISTRATIONCHECK_EMAIL_OK');
                            }
                        }
                    }
                break;
        }

        $response->send($response_text);
    }
}