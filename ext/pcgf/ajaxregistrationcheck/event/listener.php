<?php
/**
 * @copyright 2017 MarkusWME
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 */

namespace pcgf\ajaxregistrationcheck\event;

use phpbb\config\config;
use phpbb\controller\helper;
use phpbb\template\template;
use phpbb\user;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/** @version 1.0.1 (atualizado para phpBB 3.3.15) */
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
     * @access public
     * @since  1.0.0
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
     * @access public
     * @since  1.0.0
     * @return array Array with the subscribed events
     */
    static public function getSubscribedEvents()
    {
        return array(
            'core.ucp_register_data_before' => 'assign_register_data',
        );
    }

    /**
     * Encode values safely for inline JavaScript in phpBB templates.
     *
     * @param mixed $value
     * @return string
     */
    protected function json($value)
    {
        return json_encode($value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    }

    /**
     * Function to assign all needed data to the registration form
     * @access public
     * @since  1.0.0
     */
    public function assign_register_data()
    {
        // Load language data
        $this->user->add_lang('ucp');
        $this->user->add_lang_ext('pcgf/ajaxregistrationcheck', array('ajaxregistrationcheck'));
        $username_rule = $this->config['allow_name_chars'];
        switch ($username_rule)
        {
            case 'USERNAME_CHARS_ANY':
                $username_rule = "^.+$";
            break;
            case 'USERNAME_ALPHA_ONLY':
                $username_rule = "^[a-zA-Z0-9]+$";
            break;
            case 'USERNAME_ALPHA_SPACERS':
                $username_rule = "^[a-zA-Z0-9 \\-\\+_\\[\\\]]+$";
            break;
            case 'USERNAME_LETTER_NUM':
                $username_rule = "^[a-zA-Z0-9äöüÄÖÜ]+$";
            break;
            case 'USERNAME_LETTER_NUM_SPACERS':
                $username_rule = "^[a-zA-Z0-9äöüÄÖÜ \\-\\+_\\[\\\]]+$";
            break;
            case 'USERNAME_ASCII':
                $username_rule = "^[a-zA-Z0-9 !\\\"#\\$%&'\\(\\)\\*\\+,\\-\\.\\/:;<=>\\?@\\[\\\]\\^_`\\{\\|\\}~]+$";
            break;
            default:
                $username_rule = "^.+$";
            break;
        }
        $password_rule = $this->config['pass_complex'];
        switch ($password_rule)
        {
            case 'PASS_TYPE_ANY':
                $password_rule = 0;
            break;
            case 'PASS_TYPE_CASE':
                $password_rule = 10;
            break;
            case 'PASS_TYPE_ALPHA':
                $password_rule = 100;
            break;
            case 'PASS_TYPE_SYMBOL':
                $password_rule = 1000;
            break;
            default:
                $password_rule = 0;
            break;
        }
        $this->template->assign_vars(array(
            'PCGF_AJAXREGISTRATIONCHECK' => true,

            'PCGF_AJAXREGISTRATIONCHECK_USERNAME_MIN_JSON'                => $this->json((int) $this->config['min_name_chars']),
            'PCGF_AJAXREGISTRATIONCHECK_USERNAME_MAX_JSON'                => $this->json((int) $this->config['max_name_chars']),
            'PCGF_AJAXREGISTRATIONCHECK_USERNAME_RULE_JSON'               => $this->json($username_rule),
            'PCGF_AJAXREGISTRATIONCHECK_USERNAME_INVALID_BOUNDARIES_JSON' => $this->json($this->user->lang($this->config['allow_name_chars'] . '_EXPLAIN', $this->config['min_name_chars'], $this->config['max_name_chars'])),
            'PCGF_AJAXREGISTRATIONCHECK_EMAIL_RULE_JSON'                  => $this->json(get_preg_expression('email')),
            'PCGF_AJAXREGISTRATIONCHECK_PASSWORD_MIN_JSON'                => $this->json((int) $this->config['min_pass_chars']),
            'PCGF_AJAXREGISTRATIONCHECK_PASSWORD_RULE_JSON'               => $this->json((int) $password_rule),
            'PCGF_AJAXREGISTRATIONCHECK_PASSWORD_INVALID_JSON'            => $this->json($this->user->lang($this->config['pass_complex'] . '_EXPLAIN', $this->config['min_pass_chars'], '∞')),
            'PCGF_AJAXREGISTRATIONCHECK_CHECK_USERNAME_LINK_JSON'         => $this->json($this->helper->route('pcgf_ajaxregistrationcheck_controller', array('type' => 'username'))),
            'PCGF_AJAXREGISTRATIONCHECK_CHECK_EMAIL_LINK_JSON'            => $this->json($this->helper->route('pcgf_ajaxregistrationcheck_controller', array('type' => 'email'))),
            'PCGF_AJAXREGISTRATIONCHECK_EMAIL_INVALID_JSON'               => $this->json($this->user->lang('PCGF_AJAXREGISTRATIONCHECK_EMAIL_INVALID')),
            'PCGF_AJAXREGISTRATIONCHECK_CONFIRM_PASSWORD_OK_JSON'         => $this->json($this->user->lang('PCGF_AJAXREGISTRATIONCHECK_CONFIRM_PASSWORD_OK')),
            'PCGF_AJAXREGISTRATIONCHECK_CONFIRM_PASSWORD_INVALID_JSON'    => $this->json($this->user->lang('PCGF_AJAXREGISTRATIONCHECK_CONFIRM_PASSWORD_INVALID')),
            'PCGF_AJAXREGISTRATIONCHECK_PASSWORD_STRENGTH_JSON'           => $this->json($this->user->lang('PCGF_AJAXREGISTRATIONCHECK_PASSWORD_STRENGTH') . $this->user->lang('COLON')),
            'PCGF_AJAXREGISTRATIONCHECK_PASSWORD_VERY_WEAK_JSON'          => $this->json($this->user->lang('PCGF_AJAXREGISTRATIONCHECK_PASSWORD_VERY_WEAK')),
            'PCGF_AJAXREGISTRATIONCHECK_PASSWORD_WEAK_JSON'               => $this->json($this->user->lang('PCGF_AJAXREGISTRATIONCHECK_PASSWORD_WEAK')),
            'PCGF_AJAXREGISTRATIONCHECK_PASSWORD_NORMAL_JSON'             => $this->json($this->user->lang('PCGF_AJAXREGISTRATIONCHECK_PASSWORD_NORMAL')),
            'PCGF_AJAXREGISTRATIONCHECK_PASSWORD_STRONG_JSON'             => $this->json($this->user->lang('PCGF_AJAXREGISTRATIONCHECK_PASSWORD_STRONG')),
            'PCGF_AJAXREGISTRATIONCHECK_PASSWORD_VERY_STRONG_JSON'        => $this->json($this->user->lang('PCGF_AJAXREGISTRATIONCHECK_PASSWORD_VERY_STRONG')),
            'PCGF_AJAXREGISTRATIONCHECK_LOADING_JSON'                     => $this->json($this->user->lang('LOADING') . '...'),
        ));
    }
}