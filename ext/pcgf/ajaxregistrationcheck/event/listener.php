<?php

/**
 * @author    MarkusWME <markuswme@pcgamingfreaks.at>
 * @copyright 2017 MarkusWME
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 */

namespace pcgf\ajaxregistrationcheck\event;

use phpbb\config\config;
use phpbb\controller\helper;
use phpbb\template\template;
use phpbb\user;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/** @version 1.0.0 */
class listener implements EventSubscriberInterface
{
    /** @var config $config Configuration object */
    protected $config;

    /** @var helper $helper Controller helper object */
    protected $helper;

    /** @var template $template Template object */
    protected $template;

    /** @var user $user User object */
    protected $user;

    /**
     * Listener constructor
     *
     * @access public
     * @since  1.0.0
     *
     * @param config   $config   Configuration object
     * @param helper   $helper   Controller helper object
     * @param template $template Template object
     * @param user     $user     User object
     */
    public function __construct(config $config, helper $helper, template $template, user $user)
    {
        $this->config = $config;
        $this->helper = $helper;
        $this->template = $template;
        $this->user = $user;
    }

    /**
     * Function that returns the subscribed events
     *
     * @access public
     * @since  1.0.0
     *
     * @return array Array with the subscribed events
     */
    static public function getSubscribedEvents()
    {
        return array(
            'core.ucp_register_data_before' => 'assign_register_data',
        );
    }

    /**
     * Function to assign all needed data to the registration form
     *
     * @access public
     * @since  1.0.0
     */
    public function assign_register_data()
    {
        // Load language data
        $this->user->add_lang('ucp');
        $this->user->add_lang_ext('pcgf/ajaxregistrationcheck', array('ajaxregistrationcheck'));
        $this->template->assign_vars(array(
            'PCGF_AJAXREGISTRATIONCHECK'        => true,
            // json_encode() with hex flags per project security standards (prevents XSS)
            'PCGF_AJAXREGISTRATIONCHECK_CONFIG' => $this->get_json_config(),
        ));
    }

    /**
     * Build the client side configuration as a JSON encoded string
     *
     * @access private
     * @since  1.1.1
     *
     * @return string JSON encoded configuration object
     */
    private function get_json_config()
    {
        $config = array(
            'loading'                => $this->user->lang('LOADING') . '...',
            'usernameMin'            => (int) $this->config['min_name_chars'],
            'usernameMax'            => (int) $this->config['max_name_chars'],
            'usernameRule'           => $this->config['allow_name_chars'],
            'usernameInvalid'        => $this->user->lang($this->config['allow_name_chars'] . '_EXPLAIN', $this->config['min_name_chars'], $this->config['max_name_chars']),
            'usernameCheckLink'      => $this->helper->route('pcgf_ajaxregistrationcheck_controller', array('type' => 'username')),
            'emailInvalid'           => $this->user->lang('PCGF_AJAXREGISTRATIONCHECK_EMAIL_INVALID'),
            'emailCheckLink'         => $this->helper->route('pcgf_ajaxregistrationcheck_controller', array('type' => 'email')),
            'pwdMin'                => (int) $this->config['min_pass_chars'],
            'pwdRule'               => $this->get_password_rule(),
            'pwdInvalid'            => $this->user->lang($this->config['pass_complex'] . '_EXPLAIN', $this->config['min_pass_chars'], $this->config['max_pass_chars']),
            'pwdConfirmValid'       => $this->user->lang('PCGF_AJAXREGISTRATIONCHECK_CONFIRM_PASSWORD_OK'),
            'pwdConfirmInvalid'     => $this->user->lang('NEW_PASSWORD_ERROR'),
            'strengthLabel'          => $this->user->lang('PCGF_AJAXREGISTRATIONCHECK_PASSWORD_STRENGTH') . $this->user->lang('COLON'),
            'veryWeak'               => $this->user->lang('PCGF_AJAXREGISTRATIONCHECK_PASSWORD_VERY_WEAK'),
            'weak'                   => $this->user->lang('PCGF_AJAXREGISTRATIONCHECK_PASSWORD_WEAK'),
            'normal'                 => $this->user->lang('PCGF_AJAXREGISTRATIONCHECK_PASSWORD_NORMAL'),
            'strong'                 => $this->user->lang('PCGF_AJAXREGISTRATIONCHECK_PASSWORD_STRONG'),
            'veryStrong'             => $this->user->lang('PCGF_AJAXREGISTRATIONCHECK_PASSWORD_VERY_STRONG'),
        );
        return json_encode($config, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    }

    /**
     * Map the board's password complexity setting to the client side rule level
     *
     * @access private
     * @since  1.1.1
     *
     * @return int The password rule level (0: any, 10: mixed case, 100: + number, 1000: + symbol)
     */
    private function get_password_rule()
    {
        switch ($this->config['pass_complex'])
        {
            case 'PASS_TYPE_CASE':
                return 10;
            case 'PASS_TYPE_ALPHA':
                return 100;
            case 'PASS_TYPE_SYMBOL':
                return 1000;
            default:
                return 0;
        }
    }
}