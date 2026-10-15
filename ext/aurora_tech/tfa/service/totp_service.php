<?php
/**
 * Aurora Tech TFA — TOTP Two Factor Authentication extension for phpBB.
 *
 * @copyright (c) 2026 Aurora Tech Inc.
 * @license GPL-2.0-only
 */

namespace aurora_tech\tfa\service;

use RobThree\Auth\Providers\Qr\BaconQrCodeProvider;
use RobThree\Auth\TwoFactorAuth;

/**
 * TOTP (RFC 6238) service.
 *
 * Thin wrapper around robthree/twofactorauth that isolates the third-party
 * dependency behind a stable extension-owned API, so the library can be
 * swapped or upgraded without touching UCP/ACP/login code.
 */
class totp_service
{
	/** Number of digits in a TOTP code (standard authenticator default). */
	public const CODE_DIGITS = 6;

	/** TOTP time-step in seconds (RFC 6238 recommended default). */
	public const PERIOD_SECONDS = 30;

	/**
	 * Accepted clock-drift window, in time-steps, on each side of "now".
	 * One step (±30 s) absorbs typical phone clock drift without materially
	 * widening the brute-force surface.
	 */
	public const DISCREPANCY_WINDOW = 1;

	/** Entropy of generated secrets, in bits (160 = RFC 4226 recommendation). */
	private const SECRET_BITS = 160;

	protected \phpbb\config\config $config;

	protected ?TwoFactorAuth $tfa = null;

	/**
	 * @param \phpbb\config\config $config phpBB configuration (provides the board name used as TOTP issuer).
	 */
	public function __construct(\phpbb\config\config $config)
	{
		$this->config = $config;
	}

	/**
	 * Generate a new random base32 shared secret.
	 *
	 * @return string Base32-encoded secret (no padding).
	 */
	public function generate_secret(): string
	{
		return $this->get_tfa()->createSecret(self::SECRET_BITS);
	}

	/**
	 * Verify a user-supplied TOTP code against a shared secret.
	 *
	 * Whitespace is stripped so codes pasted as "123 456" validate. Anything
	 * that is not exactly CODE_DIGITS digits is rejected before touching the
	 * library, keeping the hot path strict.
	 *
	 * @param string   $secret Base32 shared secret.
	 * @param string   $code   User-supplied code.
	 * @param int|null $time   Unix timestamp to verify against (null = now); injectable for tests.
	 *
	 * @return bool True when the code is valid within the drift window.
	 */
	public function verify_code(string $secret, string $code, ?int $time = null): bool
	{
		$code = (string) preg_replace('/\s+/', '', $code);

		if (!preg_match('/^\d{' . self::CODE_DIGITS . '}$/', $code))
		{
			return false;
		}

		return $this->get_tfa()->verifyCode($secret, $code, self::DISCREPANCY_WINDOW, $time);
	}

	/**
	 * Build the otpauth:// provisioning URI encoded into the enrolment QR code.
	 *
	 * @param string $username Board username (QR label).
	 * @param string $secret   Base32 shared secret.
	 *
	 * @return string otpauth://totp/... URI.
	 */
	public function get_otpauth_uri(string $username, string $secret): string
	{
		return $this->get_tfa()->getQRText($username, $secret);
	}

	/**
	 * Render the enrolment QR code as an inline data URI (SVG).
	 *
	 * SVG keeps the extension free of any Imagick/GD requirement.
	 *
	 * @param string $username Board username (QR label).
	 * @param string $secret   Base32 shared secret.
	 * @param int    $size     Rendered size in pixels.
	 *
	 * @return string data: URI suitable for an <img src> attribute.
	 */
	public function get_qr_code_data_uri(string $username, string $secret, int $size = 200): string
	{
		return $this->get_tfa()->getQRCodeImageAsDataUri($username, $secret, $size);
	}

	/**
	 * Lazily build the underlying library instance.
	 *
	 * The issuer is the board name so authenticator apps display a meaningful
	 * account entry on any board running this extension.
	 *
	 * @return TwoFactorAuth Configured library instance.
	 */
	protected function get_tfa(): TwoFactorAuth
	{
		if ($this->tfa === null)
		{
			$issuer = (string) ($this->config['sitename'] ?? '');

			$this->tfa = new TwoFactorAuth(
				new BaconQrCodeProvider(4, '#ffffff', '#000000', 'svg'),
				$issuer !== '' ? $issuer : 'phpBB',
				self::CODE_DIGITS,
				self::PERIOD_SECONDS
			);
		}

		return $this->tfa;
	}
}
