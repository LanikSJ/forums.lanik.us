<?php
/**
 *
 * Guest pseudonyms. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Emma Laurijssens van Engelenhoven
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace esp\guestpseudonyms;

/**
 * Extension entry point.
 *
 * Beyond the default \phpbb\extension\base behaviour (enable/disable/purge
 * running the migrations under migrations/) this only gates the phpBB version.
 */
class ext extends \phpbb\extension\base
{
	/**
	 * The one true minimum, matching composer.json's soft-require and the core
	 * migration m1_install_schema depends on.
	 *
	 * 3.3.15 is where core.memberlist_modify_view_profile_template_vars gained
	 * the user_id variable that signature hiding reads. On anything older that
	 * variable is null, so signature hiding would silently do nothing — better
	 * to refuse to enable than to leave a member's signature on show while the
	 * UCP tells them it is hidden.
	 */
	const MIN_PHPBB_VERSION = '3.3.15';

	/**
	 * @return bool Whether this board is new enough to run the extension.
	 */
	public function is_enableable()
	{
		$config = $this->container->get('config');

		return phpbb_version_compare($config['version'], self::MIN_PHPBB_VERSION, '>=');
	}
}
