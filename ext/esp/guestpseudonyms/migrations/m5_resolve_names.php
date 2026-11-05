<?php
/**
 *
 * Guest pseudonyms. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Emma Laurijssens van Engelenhoven
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace esp\guestpseudonyms\migrations;

/**
 * Resolve quote/mention names from user_id for logged-in members too.
 *
 * Adds the ACP switch (default on) that makes quotes and @mentions show each
 * referenced account's current username for logged-in viewers, so renaming a
 * user updates old quotes without a database search-and-replace.
 */
class m5_resolve_names extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['guestpseudonyms_resolve_names']);
	}

	public static function depends_on()
	{
		return ['\esp\guestpseudonyms\migrations\m2_opt_out'];
	}

	public function update_data()
	{
		return [
			['config.add', ['guestpseudonyms_resolve_names', 1]],
		];
	}

	public function revert_data()
	{
		return [
			['config.remove', ['guestpseudonyms_resolve_names']],
		];
	}
}
