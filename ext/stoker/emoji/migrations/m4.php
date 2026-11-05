<?php
/**
 *
 * Emoji V2 extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 stoker - https://phpbb3bbcodes.com/
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

declare(strict_types=1);

namespace stoker\emoji\migrations;

/**
 * Emoji V2 migration: Noto font choice
 */
class m4 extends \phpbb\db\migration\migration
{
	/**
	 * @return bool
	 */
	public function effectively_installed(): bool
	{
		return isset($this->config['stoker_emoji_noto_full']);
	}

	/**
	 * @return array
	 */
	public static function depends_on()
	{
		return ['\stoker\emoji\migrations\m3'];
	}

	/**
	 * Add the Noto font choice: 0 = most used emoji subset, 1 = full font.
	 *
	 * @return array
	 */
	public function update_data()
	{
		return [
			['config.add', ['stoker_emoji_noto_full', 0]],
		];
	}
}
