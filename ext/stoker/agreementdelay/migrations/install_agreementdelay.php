<?php
/**
 *
 * stoker/agreementdelay
 *
 * @package stoker\agreementdelay
 * @copyright (c) 2026 stoker
 * @license GPL-2.0-only
 *
 */

namespace stoker\agreementdelay\migrations;

class install_agreementdelay extends \phpbb\db\migration\migration
{
	/**
	 * Check if migration is effectively installed
	 *
	 * @return bool
	 */
	public function effectively_installed()
	{
		return isset($this->config['agreementdelay_seconds']) && isset($this->config['agreementdelay_secret']);
	}

	/**
	 * Assign migration file dependencies
	 *
	 * @return array
	 */
	public static function depends_on()
	{
		return ['\phpbb\db\migration\data\v330\v330rc1'];
	}

	/**
	 * Add or update data in the database
	 *
	 * @return array
	 */
	public function update_data()
	{
		return [
			['config.add', ['agreementdelay_seconds', 15]],
			['config.add', ['agreementdelay_secret', bin2hex(random_bytes(32))]],
		];
	}
	
	/**
	 * Revert data on extension disable/uninstall
	 *
	 * @return array
	 */
	public function revert_data()
	{
		return [
			['config.remove', ['agreementdelay_seconds']],
			['config.remove', ['agreementdelay_secret']],
		];
	}
}