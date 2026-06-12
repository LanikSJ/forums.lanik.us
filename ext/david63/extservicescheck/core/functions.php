<?php
/**
*
* @package Extension .yml Check Extension
* @copyright (c) 2019 david63
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace david63\extservicescheck\core;

use phpbb\extension\manager;
use phpbb\user;
use phpbb\language\language;
use phpbb\log\log;
use phpbb\exception\version_check_exception;

/**
* functions
*/
class functions
{
	/** @var manager */
	protected $phpbb_extension_manager;

	/** @var user */
	protected $user;

	/** @var language */
	protected $language;

	/** @var log */
	protected $log;

	/**
	* Constructor for functions
	*
	* @param manager		$phpbb_extension_manager	Extension manager
	* @param user			$user						User object
	* @param language		$language					Language object
	* @param log			$log						Log object
	*
	* @access public
	*/
	public function __construct(manager $phpbb_extension_manager, user $user, language $language, log $log)
	{
		$this->ext_manager	= $phpbb_extension_manager;
		$this->user			= $user;
		$this->language		= $language;
		$this->log			= $log;

		$this->namespace	= __NAMESPACE__;
	}

	/**
	* Get the extension's namespace
	*
	* @return $extension_name
	* @access public
	*/
	public function get_ext_namespace($mode = 'php')
	{
		// Let's extract the extension name from the namespace
		$extension_name = substr($this->namespace, 0, -(strlen($this->namespace) - strrpos($this->namespace, '\\')));

		// Now format the extension name
		switch ($mode)
		{
			case 'php':
				$extension_name = str_replace('\\', '/', $extension_name);
			break;

			case 'twig':
				$extension_name = str_replace('\\', '_', $extension_name);
			break;
		}

		return $extension_name;
	}

	/**
	* Check if there is an updated version of the extension
	*
	* @return $new_version
	* @access public
	*/
	public function version_check()
	{
		if ($this->get_meta('host') == 'www.phpbb.com')
		{
			$port 	= 'https://';
			$stable	= null;
		}
		else
		{
			$port 	= 'http://';
			$stable = 'unstable';
		}

		// Can we access the version srver?
		if (@fopen($port . $this->get_meta('host') . $this->get_meta('directory') . '/' . $this->get_meta('filename'), 'r'))
		{
			try
			{
				$md_manager 	= $this->ext_manager->create_extension_metadata_manager($this->get_ext_namespace());
				$version_data	= $this->ext_manager->version_check($md_manager, true, false, $stable);
			}
			catch (version_check_exception $e)
			{
				$version_data['current'] = 'fail';
			}
		}
		else
		{
			$version_data['current'] = 'fail';
		}

		return $version_data;
	}

	/**
	* Get a meta_data key value
	*
	* @return $meta_data
	* @access public
	*/
	public function get_meta($data)
	{
		$meta_data	= '';
		$md_manager = $this->ext_manager->create_extension_metadata_manager($this->get_ext_namespace());

		foreach (new \RecursiveIteratorIterator(new \RecursiveArrayIterator($md_manager->get_metadata('all'))) as $key => $value)
		{
			if ($data === $key)
			{
				$meta_data = $value;
			}
		}

		return $meta_data;
	}

	/**
	 * Check that the reqirements are met for this extension
	 *
	 * @return array
	 * @access public
	 */
	public function ext_requirements()
	{
		$php_valid = $phpbb_valid = false;

		// Check the PHP version is valid
		$php_versn = htmlspecialchars_decode($this->get_meta('php'));

		if ($php_versn)
		{
			// Get the conditions
			preg_match('/\d/', $php_versn, $php_pos, PREG_OFFSET_CAPTURE);
			$php_valid = phpbb_version_compare(PHP_VERSION, substr($php_versn, $php_pos[0][1]), substr($php_versn, 0, $php_pos[0][1]));
		}

		// Check phpBB versions are valid
		$phpbb_versn = htmlspecialchars_decode($this->get_meta('phpbb/phpbb'));
		$phpbb_vers  = explode(',', $phpbb_versn);

		if ($phpbb_vers[0])
		{
			// Get the first conditions
			preg_match('/\d/', $phpbb_vers[0], $phpbb_pos_0, PREG_OFFSET_CAPTURE);
			$phpbb_valid = phpbb_version_compare(PHPBB_VERSION, substr($phpbb_vers[0], $phpbb_pos_0[0][1]), substr($phpbb_vers[0], 0, $phpbb_pos_0[0][1]));

			if ($phpbb_vers[1] && !$phpbb_valid)
			{
				// Get the second conditions
				preg_match('/\d/', $phpbb_vers[1], $phpbb_pos_1, PREG_OFFSET_CAPTURE);
				$phpbb_valid = phpbb_version_compare(PHPBB_VERSION, substr($phpbb_vers[0], $phpbb_pos_0[0][1]), substr($phpbb_vers[0], 0, $phpbb_pos_0[0][1]));
			}
		}

		return [$php_valid, $phpbb_valid];
	}

	/**
	* Get the meta data for the extensions
	*
	* @return $extension_meta_data
	* @access public
	*/
	public function extension_meta_data()
	{
		$extension_meta_data = [];

		foreach ($this->ext_manager->all_available() as $name => $location)
		{
			$md_manager = $this->ext_manager->create_extension_metadata_manager($name);
			$meta_data	= $md_manager->get_metadata('all');

			$ext_status = ($this->ext_manager->is_enabled($name)) ? $this->language->lang('ENABLED') : '';
			$ext_status = ($this->ext_manager->is_disabled($name)) ? $this->language->lang('DISABLED') : $ext_status;
			$ext_status = ($this->ext_manager->is_configured($name)) ? $ext_status : $this->language->lang('DORMANT');

			$extension_meta_data[$name] = array(
				'EXT_ENABLED'		=> $this->ext_manager->is_enabled($name),
				'EXT_STATUS'		=> $ext_status,
				'LOCATION'			=> $location,
				'META_DISPLAY_NAME'	=> $meta_data['extra']['display-name'],
				'META_NAME'			=> $meta_data['name'],
				'META_VERSION' 		=> $meta_data['version'],
				'VENDOR'			=> strtok($meta_data['name'], '/'),
			);
		}

		return $extension_meta_data;
	}

	/**
	* Disable an extension
	*
	* @return null
	* @access public
	*/
	public function extension_disable($ext_name)
	{
		while ($this->ext_manager->disable_step($ext_name))
		{
			continue;
		}

		// Add disable action to the admin log
		$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_EXTN_DISABLE', time(), array($ext_name));
	}

	/**
	* Sort helper for the array containing the metadata about the extensions.
	*/
	public function sort_extension_meta_data_table($val1, $val2)
	{
		return strnatcasecmp($val1['META_DISPLAY_NAME'], $val2['META_DISPLAY_NAME']);
	}
}
