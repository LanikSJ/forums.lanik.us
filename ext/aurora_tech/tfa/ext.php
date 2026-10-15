<?php
/**
 * Aurora Tech TFA — TOTP Two Factor Authentication extension for phpBB.
 *
 * @copyright (c) 2026 Aurora Tech Inc.
 * @license GPL-2.0-only
 */

namespace aurora_tech\tfa;

/**
 * Extension base class.
 *
 * Guards activation so the extension can only be enabled on a board
 * that meets the minimum runtime requirements (PHP 8.2+, phpBB 3.3+).
 * This prevents the class of failure seen with legacy TFA extensions
 * that activate cleanly and then fatal on first use.
 */
class ext extends \phpbb\extension\base
{
	/** Minimum PHP version required by the TOTP library (robthree/twofactorauth v3). */
	private const MIN_PHP_VERSION_ID = 80200;

	/** Minimum phpBB version this extension targets. */
	private const MIN_PHPBB_VERSION = '3.3.0';

	/**
	 * Check whether the extension can be enabled on this board.
	 *
	 * Besides the runtime requirements (PHP 8.2+, phpBB 3.3+), this also
	 * refuses to enable an incomplete copy of the extension: the repository
	 * source ZIP does not bundle the composer dependencies (vendor/), which
	 * would otherwise fatal on first use ("Class RobThree\Auth\TwoFactorAuth
	 * not found") or show a blank page when display_errors is off.
	 *
	 * @return bool|string[] True when the board satisfies every requirement,
	 *                       otherwise a list of human-readable reasons
	 *                       (displayed by the ACP, phpBB 3.3+).
	 */
	public function is_enableable()
	{
		$errors = [];

		if (PHP_VERSION_ID < self::MIN_PHP_VERSION_ID)
		{
			$errors[] = 'Aurora Tech TFA requires PHP 8.2 or later.';
		}

		if (!phpbb_version_compare(PHPBB_VERSION, self::MIN_PHPBB_VERSION, '>='))
		{
			$errors[] = 'Aurora Tech TFA requires phpBB 3.3.0 or later.';
		}

		if (!$this->has_bundled_dependencies())
		{
			$errors[] = 'This copy of Aurora Tech TFA is incomplete: the bundled libraries (vendor/) are missing. '
				. 'Please install the packaged release archive (which includes vendor/) instead of the repository source ZIP, '
				. 'or run "composer install --no-dev" inside ext/aurora_tech/tfa/.';
		}

		return $errors === [] ? true : $errors;
	}

	/**
	 * Whether the composer dependencies shipped with the extension are present.
	 *
	 * Extracted for testability; overridden in tests to simulate an
	 * incomplete installation (repository source ZIP without vendor/).
	 *
	 * @return bool
	 */
	protected function has_bundled_dependencies(): bool
	{
		return file_exists(__DIR__ . '/vendor/autoload.php');
	}
}
