<?php
/**
*
* @package phpBB Extension - Password Generator
* @copyright (c) 2019 HiFiKabin
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace hifikabin\password;

/**
* Extension class for custom enable/disable/purge actions
*/
class ext extends \phpbb\extension\base
{
	/**
	* Enable extension if phpBB version requirement is met
	*
	* @return bool
	* @access public
	*/
	public function is_enableable()
	{
		return phpbb_version_compare(PHPBB_VERSION, '3.2.0@dev', '>=');
	}
}
