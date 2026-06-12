<?php
/**
*
* @package phpBB Extension - Spamsecure from 69bruno
* @copyright (c) 2021 (cruiser-lounge.de)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace bruno\spamsecure\controller;

class acp_controller
{
	protected $extension_manager;
	protected $path_helper;
	protected $config;
	protected $language;
	protected $request;
	protected $template;
	protected $user;
	protected $root_path;
	protected $php_ext;
	protected $u_action;

	public function __construct(
		\phpbb\extension\manager $ext_manager,
		\phpbb\path_helper $path_helper,
		\phpbb\config\config $config,
		\phpbb\language\language $language,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		$root_path,
		$php_ext
	)
	{
		$this->md_manager 			= $ext_manager->create_extension_metadata_manager('bruno/spamsecure');
		$this->admin_path 			= $path_helper->get_phpbb_root_path() . $path_helper->get_adm_relative_path();
		$this->config				= $config;
		$this->language				= $language;
		$this->request				= $request;
		$this->template				= $template;
		$this->user					= $user;
		$this->php_ext				= $php_ext;
		$this->root_path			= $root_path;
	}

	public function display_options()
	{
		$this->language->add_lang('acp_spamsecure', 'bruno/spamsecure');
		$ext_display_ver 			= $this->md_manager->get_metadata('version');
		$ext_lang_min_ver 			= $this->md_manager->get_metadata()['extra']['lang-min-ver'];
		$ext_display_name 			= $this->md_manager->get_metadata('display-name');
		$lang_ver					= $this->language->is_set('SPSE_LANG_EXT_VER') ? $this->language->lang('SPSE_LANG_EXT_VER') : '0.0.0';
		$notes 						= '';
		add_form_key('bruno_spamsecure_acp');
		$errors = [];

		if ($this->request->is_set_post('submit'))
		{
			if (!check_form_key('bruno_spamsecure_acp'))
			{
				$errors[] = $this->language->lang('FORM_INVALID');
			}

			if (empty($errors))
			{
				$this->config->set('spamsecure_search_complete', $this->request->variable('spamsecure_search_complete', 0));
				$this->config->set('spamsecure_invalid_chars_lang_kyrill', $this->request->variable('spamsecure_invalid_chars_lang_kyrill', 0));
				$this->config->set('spamsecure_invalid_chars_lang_chinese', $this->request->variable('spamsecure_invalid_chars_lang_chinese', 0));
				$this->config->set('spamsecure_invalid_chars_lang_hindi', $this->request->variable('spamsecure_invalid_chars_lang_hindi', 0));
				$this->config->set('spamsecure_no_links', $this->request->variable('spamsecure_no_links', 0));
				$this->config->set('spamsecure_invalid_chars_lang_custom', $this->request->variable('spamsecure_invalid_chars_lang_custom', 0));
				$this->config->set('spamsecure_invalid_chars_input_custom', $this->request->variable('spamsecure_invalid_chars_input_custom', '', true));
				$this->config->set('spamsecure_invalid_chars_counter_switch', $this->request->variable('spamsecure_invalid_chars_counter_switch', 0));
				$this->config->set('spamsecure_invalid_chars_counter', $this->request->variable('spamsecure_invalid_chars_counter', ''));
				$this->config->set('spamsecure_regex_individual_check', $this->request->variable('spamsecure_regex_individual_check', 0));
				$invalid_regex_msg = '';
				$regex_string = $this->request->variable('spamsecure_invalid_regex', '', true);
				if ($regex_string != '' && @preg_match($regex_string, '') === false)
				{
					$message_output_start = '<br><br><span style="background-color: #BC2A4D; color: #FFFFFF; padding: 5px 10px; font-weight: bold; font-size: 15px;">';
					$message_output_end = '</span>';
					$invalid_regex_msg = $message_output_start . $this->language->lang('SPSE_INVALID_REGEX_MSG') . $message_output_end;
				}
				else
				{
					$this->config->set('spamsecure_invalid_regex',  $regex_string );
				}
				trigger_error($this->language->lang('SPSE_SETTING_SAVED') . $invalid_regex_msg . adm_back_link($this->u_action));
			}
		}

		$s_errors = !empty($errors);

		if (!phpbb_version_compare($lang_ver, $ext_lang_min_ver, '>='))
		{
			$this->add_note($notes, $this->language->lang('SPSE_LANGUAGEPACK_OUTDATED'));
		}

		$this->template->assign_vars([
			'SPSE_S_ERROR'								=> $s_errors,
			'SPSE_ERROR_MSG'							=> $s_errors ? implode('<br>', $errors) : '',
			'SPSE_NOTES'								=> $notes,
			'SPSE_EXT_NAME'								=> $ext_display_name,
			'SPSE_EXT_VER'								=> $ext_display_ver,
			'SPSE_SEARCH_COMPLETE'						=> $this->config['spamsecure_search_complete'],
			'SPSE_LANG_KYRILL'							=> $this->config['spamsecure_invalid_chars_lang_kyrill'],
			'SPSE_LANG_CHINESE'							=> $this->config['spamsecure_invalid_chars_lang_chinese'],
			'SPSE_LANG_HINDI'							=> $this->config['spamsecure_invalid_chars_lang_hindi'],
			'SPSE_LANG_CUSTOM'							=> $this->config['spamsecure_invalid_chars_lang_custom'],
			'SSPE_NO_LINKS'								=> $this->config['spamsecure_no_links'],
			'SSPE_REGEX_INDIVIDUAL_CHECK'				=> $this->config['spamsecure_regex_individual_check'],
			'SPSE_INVALID_CHARS'						=> $this->config['spamsecure_invalid_chars_input_custom'],
			'SPSE_INVALID_CHARS_COUNTER_SWITCH'			=> $this->config['spamsecure_invalid_chars_counter_switch'],
			'SPSE_INVALID_CHARS_COUNTER'				=> $this->config['spamsecure_invalid_chars_counter'],
			'SPSE_INVALID_REGEX'						=> $this->config['spamsecure_invalid_regex'],
			'U_USER_PERMISSIONS'						=> append_sid("{$this->admin_path}index.$this->php_ext" ,'i=permissions&amp;mode=setting_user_global'),
			'U_GROUP_PERMISSIONS'						=> append_sid("{$this->admin_path}index.$this->php_ext" ,'i=permissions&amp;mode=setting_group_global'),
			'U_ACTION'									=> $this->u_action,
		]);
	}
	private function add_note(string &$notes, $msg)
	{
		$notes .= (($notes != '') ? "\n" : "") . sprintf('<p>%s</p>', $msg);

	}

	public function set_page_url($u_action)
	{
		$this->u_action = $u_action;
	}
}
