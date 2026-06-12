<?php
/**
*
* @package Extension .yml Check Extension
* @copyright (c) 2019 david63
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace david63\extservicescheck\controller;

use phpbb\request\request;
use phpbb\template\template;
use phpbb\language\language;
use david63\extservicescheck\core\functions;
use david63\extservicescheck\core\yml_formatter;

class admin_controller
{
	/** @var request */
	protected $request;

	/** @var template */
	protected $template;

	/** @var language */
	protected $language;

	/** @var functions */
	protected $functions;

	/** @var yml_formatter */
	protected $yml_formatter;

	/** @var string */
	protected $ext_images_path;

	/** @var string Custom form action */
	protected $u_action;

	/**
	* Constructor for admin_controller
	*
	* @param request		$request			Request object
	* @param template		$template			Template object
	* @param language		$language			Language object
	* @param functions		$functions			Functions for the extension
	* @param yml_formatter	$yml_formatter		yml_formatter for the extension
	* @param string			$ext_images_path	Path to this extension's images
	*
	* @return \david63\extservicescheck\controller\admin_controller
	* @access public
	*/
	public function __construct(request $request, template $template, language $language, functions $functions, yml_formatter $yml_formatter, $ext_images_path)
	{
		$this->request			= $request;
		$this->template			= $template;
		$this->language			= $language;
		$this->functions		= $functions;
		$this->yml_formatter	= $yml_formatter;
		$this->ext_images_path	= $ext_images_path;
	}

	/**
	* Display the output from this extension
	*
	* @return null
	* @access public
	*/
	public function display_output()
	{
		// Add the language files
		$this->language->add_lang(array('acp_extservicescheck', 'acp_common'), $this->functions->get_ext_namespace());

		// Get the variables
		$disable 	= $this->request->variable('disable', '');
		$ext_name	= $this->request->variable('ext_name', '');

		$back = false;

		// Are we disabling an extension?
		if ($disable == 'disable')
		{
			$this->functions->extension_disable($ext_name);
		}

		$legend_alert = $legend_error = $legend_issue = $legend_query = false;

		// Get an array of the extensions and sort into alphbetical order
		$extension_meta_data = $this->functions->extension_meta_data();
		uasort($extension_meta_data, array($this->functions, 'sort_extension_meta_data_table'));

		// Display the extensions
		foreach ($extension_meta_data as $block_vars)
		{
			$config_dir	= $block_vars['LOCATION'] . 'config/';
			$ext_name	= $block_vars['META_NAME'];
			$vendor		= $block_vars['VENDOR'];

			$this->template->assign_block_vars('ext_row', array(
				'DISPLAY_NAME'	=> $block_vars['META_DISPLAY_NAME'],

				'EXT_ENABLED'	=> $block_vars['EXT_ENABLED'],
				'EXT_STATUS'	=> $block_vars['EXT_STATUS'],

				'META_NAME'		=> $block_vars['META_NAME'],

				'U_ACTION' 		=> "{$this->u_action}&amp;disable=disable&amp;ext_name=$ext_name",

				'VENDOR'		=> $vendor,
				'VERSION'		=> $block_vars['META_VERSION'],
			));

			if (is_dir($config_dir))
			{
				$yaml_files	= [];
				$status 	= '';
				$files 		= array_diff(scandir($config_dir), array('..', '.'));

				// Create an array of all config folder(s) & files
				foreach ($files as $filename)
				{
					// Ignore any .htaccess files (which should not be there!)
					if ($filename != '.htaccess')
					{
						// Is this a file or dir?
						if (strpos($filename, '.'))
						{
							$yaml_files[$filename] = $config_dir . $filename;
						}
						else
						{
							$sub_files = array_diff(scandir($config_dir . $filename), array('..', '.'));

							foreach ($sub_files as $sub_filename)
							{
								$yaml_files[$sub_filename] = $config_dir . $filename . '/' . $sub_filename;
							}
						}
					}
				}

				// Now we can check the files
				foreach ($yaml_files as $yml_file => $filename)
				{
					// Check the namespace
					$ext_namespace = str_replace($vendor . '/', '', $ext_name);

					if (!preg_match('/^[a-z0-9\/]+$/', $ext_namespace) // Check for non alphnumeric or lowercase characters
						|| !ctype_alpha($ext_name[0]) // Check first character is alpha
						|| !ctype_alpha($ext_namespace[0]))
					{
						$legend_query = true;

						$this->template->assign_block_vars('ext_row.file_data', array(
							'STATUS' 		=> $this->language->lang('INVALID_CHRACTERS', $ext_name),
							'STATUS_IMAGE'	=> 'query',
						));
					}

					// Check that the file is accessible
					if (!is_readable($filename))
					{
						$legend_query = true;

						$this->template->assign_block_vars('ext_row.file_data', array(
							'STATUS' 		=> $this->language->lang('FILE_NOT_READABLE', $yml_file),
							'STATUS_IMAGE'	=> 'query',
						));
					}
					else if (!$file_contents = file_get_contents($filename))
					{
						if (!empty($file_contents))
						{
							$legend_query = true;

							$this->template->assign_block_vars('ext_row.file_data', array(
								'STATUS' 		=> $this->language->lang('FILE_NOT_ACCESSIBLE', $yml_file),
								'STATUS_IMAGE'	=> 'query',
							));
						}
						else
						{
							$legend_query = true;

							$this->template->assign_block_vars('ext_row.file_data', array(
								'STATUS' 		=> $this->language->lang('FILE_EMPTY', $yml_file),
								'STATUS_IMAGE'	=> 'query',
							));
						}
					}
					else if (preg_match('/\-\ \@|\-\ \%|\[\@|\[\%|\:\ \%/', $file_contents) // Check for quotes
						|| strstr($file_contents, 'pattern:') // Check for "path" not "pattern"
						|| strstr($file_contents, 'scope: prototype') // Check the "scope"
						|| strstr($file_contents, 'scope: container')
						|| strstr($file_contents, 'scope: request'))
					{
						$legend_error = true;

						$this->template->assign_block_vars('ext_row.file_data', array(
							// Create a unique key for the js script
							'FILE_KEY'		=> rand(),

							'NEW_FILE'		=> $this->yml_formatter->yaml_format($filename, $yml_file),
							'NEW_FILE_KEY'	=> rand(),

							'OLD_FILE'		=> $file_contents,

							'STATUS'		=> $this->language->lang('CONFIG_FILE_FAIL', $yml_file),
							'STATUS_IMAGE'	=> 'error',
						));
					}
					else if ($yml_file == 'services.yml' && !strstr($file_contents, 'public:')) // Check for public
					{
						$legend_issue = true;

						$this->template->assign_block_vars('ext_row.file_data', array(
							// Create a unique key for the js script
							'FILE_KEY'		=> rand(),

							'NEW_FILE'		=> $this->yml_formatter->yaml_format($filename, $yml_file),
							'NEW_FILE_KEY'	=> rand(),

							'OLD_FILE'		=> $file_contents,

							'STATUS' 		=> $this->language->lang('NOT_PUBLIC', $yml_file),
							'STATUS_IMAGE'	=> 'issue',
						));
					}
					else
					{
						// No problems found with this file
						$this->template->assign_block_vars('ext_row.file_data', array(
							'STATUS' 		=> $this->language->lang('CONFIG_FILE_PASS', $yml_file),
							'STATUS_IMAGE'	=> 'ok',
						));
					};
				}
			}
			else
			{
				// There is no config directory for this extension
				$this->template->assign_block_vars('ext_row.file_data', array(
					'STATUS' 		=> $this->language->lang('NO_CONFIG_FILES'),
					'STATUS_IMAGE'	=> 'ok',
				));
			}
		}

		$this->template->assign_vars(array(
			'DISABLE_EXPLAIN'			=> '<img src="' . $this->ext_images_path . '/disable.png" /> ' . $this->language->lang('DISABLE_EXPLAIN'),

			'ERROR_EXPLAIN'				=> '<img src="' . $this->ext_images_path . '/error.png" /> ' . $this->language->lang('ERROR_EXPLAIN'),
			'EXTENSION_QUERY_EXPLAIN'	=> '<img src="' . $this->ext_images_path . '/query_extn.png" /> ' . $this->language->lang('EXTENSION_QUERY_EXPLAIN'),
			'EXT_IMAGE_PATH' 			=> $this->ext_images_path,

			'FILE_EXPLAIN'				=> '<img src="' . $this->ext_images_path . '/compare_open.png" /> ' . $this->language->lang('FILE_EXPLAIN'),
			'FILE_OPEN_EXPLAIN'			=> '<img src="' . $this->ext_images_path . '/compare_close.png" /> ' . $this->language->lang('FILE_OPEN_EXPLAIN'),
			'FILE_QUERY_EXPLAIN'		=> '<img src="' . $this->ext_images_path . '/query_file.png" /> ' . $this->language->lang('FILE_QUERY_EXPLAIN'),

			'ISSUE_EXPLAIN'				=> '<img src="' . $this->ext_images_path . '/issue.png" /> ' . $this->language->lang('ISSUE_EXPLAIN'),
			'ISSUE_OPEN_EXPLAIN'		=> '<img src="' . $this->ext_images_path . '/issue_file.png" /> ' . $this->language->lang('ISSUE_OPEN_EXPLAIN'),

			'OK_EXPLAIN'				=> '<img src="' . $this->ext_images_path . '/ok.png" /> ' . $this->language->lang('OK_EXPLAIN'),

			'S_LEGEND_ALERT'			=> $legend_alert,
			'S_LEGEND_ERROR'			=> $legend_error,
			'S_LEGEND_ISSUE'			=> $legend_issue,
			'S_LEGEND_QUERY'			=> $legend_query,
		));

		// Template vars for header panel
		$version_data	= $this->functions->version_check();

		// Are the PHP and phpBB versions valid for this extension?
		$valid = $this->functions->ext_requirements();

		$this->template->assign_vars(array(
			'DOWNLOAD'			=> (array_key_exists('download', $version_data)) ? '<a class="download" href =' . $version_data['download'] . '>' . $this->language->lang('NEW_VERSION_LINK') . '</a>' : '',

			'HEAD_TITLE'		=> $this->language->lang('EXT_SERVICES_CHECK'),
			'HEAD_DESCRIPTION'	=> $this->language->lang('EXT_SERVICES_CHECK_EXPLAIN'),

			'NAMESPACE'			=> $this->functions->get_ext_namespace('twig'),

			'PHP_VALID' 		=> $valid[0],
			'PHPBB_VALID' 		=> $valid[1],

			'S_BACK'			=> $back,
			'S_VERSION_CHECK'	=> (array_key_exists('current', $version_data)) ? $version_data['current'] : false,

			'VERSION_NUMBER'	=> $this->functions->get_meta('version'),
		));
	}

	/**
	* Set page url
	*
	* @param string $u_action Custom form action
	* @return null
	* @access public
	*/
	public function set_page_url($u_action)
	{
		$this->u_action = $u_action;
	}
}
