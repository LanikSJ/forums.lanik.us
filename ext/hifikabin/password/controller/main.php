<?php
/**
*
* @package phpBB Extension - Password Generator
* @copyright (c) 2019 HiFiKabin
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace hifikabin\password\controller;

class main
{

	/* @var \phpbb\template\template */
	protected $template;

	/* @var \phpbb\user */
	protected $user;

	/* @var \phpbb\controller\helper */
	protected $helper;

	public function __construct(\phpbb\template\template $template, \phpbb\user $user, \phpbb\controller\helper $helper)
	{
		$this->template = $template;
		$this->user = $user;
		$this->helper = $helper;
	}

	public function base()
	{
		$this->template->assign_block_vars('navlinks', array(
			'FORUM_NAME' 	=> ($this->user->lang['PASSWORD']),
		));

	return $this->helper->render('password.html', $this->user->lang['PASSWORD']);
	}
}
