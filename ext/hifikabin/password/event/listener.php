<?php
/**
*
* @package phpBB Extension - Password Generator
* @copyright (c) 2019 HiFiKabin
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace hifikabin\password\event;

/**
* @ignore
*/
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
* Event listener
*/
class listener implements EventSubscriberInterface
{
	/* @var \phpbb\template\template */
	protected $template;

	/* @var \phpbb\controller\helper */
	protected $helper;

	/* @var \phpbb\controller\config */
	protected $config;

	/* @var \phpbb\controller\language */
	protected $language;

	static public function getSubscribedEvents()
	{
		return array(
			'core.user_setup'		=> 'load_lang',
			'core.page_header'		=> 'add_page_header_link',
		);
	}

	public function __construct(\phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\config\config $config, \phpbb\language\language $language)
	{
		$this->helper		= $helper;
		$this->template		= $template;
		$this->config		= $config;
		$this->language		= $language;
	}

	public function load_lang()
	{
		$this->language->add_lang('password', 'hifikabin/password');
	}

	public function add_page_header_link()
	{
		$this->template->assign_vars(array(
			'U_PASSWORD_GENERATOR'					=> $this->helper->route('hifikabin_password_controller'),
		));
	}
}
