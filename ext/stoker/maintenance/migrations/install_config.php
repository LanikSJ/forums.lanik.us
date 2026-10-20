<?php
/**
 *
 * Maintenance extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 stoker - https://phpbb3bbcodes.com/
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

declare(strict_types=1);

namespace stoker\maintenance\migrations;

class install_config extends \phpbb\db\migration\migration
{
	/**
	 * @return bool
	 */
	public function effectively_installed(): bool
	{
		return $this->config->offsetExists('stoker_maintenance_start');
	}

	/**
	 * @return array
	 */
	public static function depends_on(): array
	{
		return ['\phpbb\db\migration\data\v330\v330'];
	}

	/**
	 * @return array
	 */
	public function update_data(): array
	{
		return [
			['config.add', ['stoker_maintenance_enabled', 0]],
			['config.add', ['stoker_maintenance_start', 0]],
			['config.add', ['stoker_maintenance_end', 0]],
			['config.add', ['stoker_maintenance_disabled_by_schedule', 0]],
			['config.add', ['stoker_maintenance_heading', '']],
			['config.add', ['stoker_maintenance_banner_enabled', 0]],
			['config.add', ['stoker_maintenance_banner_lead_hours', 0]],
			['config.add', ['stoker_maintenance_banner_url', '']],
			['config_text.add', ['stoker_maintenance_message', '']],
		];
	}
}
