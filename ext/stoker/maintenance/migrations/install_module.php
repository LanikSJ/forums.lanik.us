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

class install_module extends \phpbb\db\migration\migration
{
	/**
	 * @return bool
	 */
	public function effectively_installed(): bool
	{
		return $this->config->offsetExists('stoker_maintenance_module_installed');
	}

	/**
	 * @return array
	 */
	public static function depends_on(): array
	{
		return ['\stoker\maintenance\migrations\install_config'];
	}

	/**
	 * @return array
	 */
	public function update_data(): array
	{
		return [
			// Parent category entry under the Extensions tab
			['module.add', [
				'acp',
				'ACP_CAT_DOT_MODS',
				'ACP_STOKER_MAINTENANCE_TITLE',
			]],
			// Our settings module under that category
			['module.add', [
				'acp',
				'ACP_STOKER_MAINTENANCE_TITLE',
				[
					'module_basename'	=> '\stoker\maintenance\acp\main_module',
					'modes'				=> ['settings'],
				],
			]],
			['config.add', ['stoker_maintenance_module_installed', 1]],
		];
	}
}
