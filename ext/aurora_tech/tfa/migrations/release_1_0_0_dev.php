<?php
/**
 * Aurora Tech TFA — TOTP Two Factor Authentication extension for phpBB.
 *
 * @copyright (c) 2026 Aurora Tech Inc.
 * @license GPL-2.0-only
 */

namespace aurora_tech\tfa\migrations;

/**
 * Baseline migration.
 *
 * Empty by design at the scaffold stage (M1): it anchors the migration
 * chain so the schema migrations of milestone M2 (TOTP secrets and
 * backup-code tables) can declare a dependency on it.
 */
class release_1_0_0_dev extends \phpbb\db\migration\migration
{
	/**
	 * Declare the phpBB core migration this baseline depends on.
	 *
	 * @return string[] Fully qualified migration class names.
	 */
	public static function depends_on()
	{
		return ['\phpbb\db\migration\data\v330\v330'];
	}
}
