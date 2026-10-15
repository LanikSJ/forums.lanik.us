<?php
/**
 * Aurora Tech TFA — TOTP Two Factor Authentication extension for phpBB.
 *
 * @copyright (c) 2026 Aurora Tech Inc.
 * @license GPL-2.0-only
 */

namespace aurora_tech\tfa\service;

use aurora_tech\tfa\storage\totp_repository_interface;

/**
 * TFA orchestration facade.
 *
 * Single entry point used by the UCP module, the login listener and the
 * ACP module. Combines the TOTP engine, the secret cipher, the backup
 * codes and the enrolment repository so no consumer ever handles a raw
 * secret or a repository directly.
 */
class tfa_manager
{
	/**
	 * Lifetime of a pending (not yet confirmed) enrolment payload, in
	 * seconds. Beyond this, the user must restart activation with a
	 * fresh secret.
	 */
	public const PENDING_TTL_SECONDS = 900;

	protected totp_service $totp;

	protected secret_cipher $cipher;

	protected backup_codes_service $backup_codes;

	protected totp_repository_interface $totp_repository;

	/**
	 * @param totp_service              $totp            TOTP engine.
	 * @param secret_cipher             $cipher          Secret encryption.
	 * @param backup_codes_service      $backup_codes    Backup-code handling.
	 * @param totp_repository_interface $totp_repository Enrolment persistence.
	 */
	public function __construct(
		totp_service $totp,
		secret_cipher $cipher,
		backup_codes_service $backup_codes,
		totp_repository_interface $totp_repository
	)
	{
		$this->totp = $totp;
		$this->cipher = $cipher;
		$this->backup_codes = $backup_codes;
		$this->totp_repository = $totp_repository;
	}

	/**
	 * Check whether a user has TFA active.
	 *
	 * @param int $user_id Board user id.
	 *
	 * @return bool True when enrolled.
	 */
	public function is_enabled(int $user_id): bool
	{
		return $this->totp_repository->is_enrolled($user_id);
	}

	/**
	 * Describe the TFA state of a user (UCP overview / ACP statistics).
	 *
	 * @param int $user_id Board user id.
	 *
	 * @return array{enabled: bool, activated_at: int, last_used_at: int, backup_codes_remaining: int}
	 *         Status summary.
	 */
	public function get_status(int $user_id): array
	{
		$enrolment = $this->totp_repository->get_enrolment($user_id);

		return [
			'enabled'					=> $enrolment !== null,
			'activated_at'				=> $enrolment['activated_at'] ?? 0,
			'last_used_at'				=> $enrolment['last_used_at'] ?? 0,
			'backup_codes_remaining'	=> $enrolment !== null ? $this->backup_codes->count_remaining($user_id) : 0,
		];
	}

	/**
	 * Begin enrolment: generate a secret and everything the activation
	 * screen needs.
	 *
	 * The secret is NOT persisted yet. It travels to the confirmation
	 * step inside an encrypted, time-stamped payload, so an activation
	 * abandoned at the QR screen leaves no trace.
	 *
	 * @param string $username Board username (QR label).
	 * @param int    $time     Unix timestamp (payload issue time).
	 *
	 * @return array{secret: string, otpauth_uri: string, qr_data_uri: string, pending_payload: string}
	 *         Data for the activation screen.
	 */
	public function start_enrolment(string $username, int $time): array
	{
		$secret = $this->totp->generate_secret();

		return [
			'secret'			=> $secret,
			'otpauth_uri'		=> $this->totp->get_otpauth_uri($username, $secret),
			'qr_data_uri'		=> $this->totp->get_qr_code_data_uri($username, $secret),
			'pending_payload'	=> $this->cipher->encrypt((string) json_encode([
				'secret'	=> $secret,
				'issued_at'	=> $time,
			])),
		];
	}

	/**
	 * Re-derive the activation screen data from a pending payload, so a
	 * failed first-code attempt redisplays the SAME QR code instead of
	 * silently rotating the secret under the user's feet.
	 *
	 * @param string $pending_payload Encrypted payload from start_enrolment().
	 * @param string $username        Board username (QR label).
	 * @param int    $time            Unix timestamp (TTL check).
	 *
	 * @return array{secret: string, otpauth_uri: string, qr_data_uri: string, pending_payload: string}|null
	 *         Screen data, or null when the payload is invalid or expired.
	 */
	public function describe_pending(string $pending_payload, string $username, int $time): ?array
	{
		$secret = $this->open_pending($pending_payload, $time);

		if ($secret === null)
		{
			return null;
		}

		return [
			'secret'			=> $secret,
			'otpauth_uri'		=> $this->totp->get_otpauth_uri($username, $secret),
			'qr_data_uri'		=> $this->totp->get_qr_code_data_uri($username, $secret),
			'pending_payload'	=> $pending_payload,
		];
	}

	/**
	 * Complete enrolment: verify the first code against the pending
	 * secret, persist the enrolment and issue backup codes.
	 *
	 * Requiring a valid first code guarantees the user really scanned
	 * the QR before TFA turns on — nobody gets locked out by activating
	 * blind.
	 *
	 * @param int    $user_id         Board user id.
	 * @param string $pending_payload Encrypted payload from start_enrolment().
	 * @param string $code            First TOTP code typed by the user.
	 * @param int    $time            Unix timestamp.
	 *
	 * @return string[]|null The one-time-displayed backup codes on
	 *                       success, null when the payload is invalid/expired
	 *                       or the code is wrong.
	 */
	public function complete_enrolment(int $user_id, string $pending_payload, string $code, int $time): ?array
	{
		$secret = $this->open_pending($pending_payload, $time);

		if ($secret === null || !$this->totp->verify_code($secret, $code, $time))
		{
			return null;
		}

		$this->totp_repository->enrol($user_id, $this->cipher->encrypt($secret), $time);

		return $this->backup_codes->generate_and_store($user_id);
	}

	/**
	 * Verify a TOTP code at login time.
	 *
	 * @param int    $user_id Board user id.
	 * @param string $code    User-supplied TOTP code.
	 * @param int    $time    Unix timestamp.
	 *
	 * @return bool True when valid; last-used marker is updated.
	 */
	public function verify_totp_login(int $user_id, string $code, int $time): bool
	{
		$encrypted = $this->totp_repository->get_encrypted_secret($user_id);

		if ($encrypted === null)
		{
			return false;
		}

		try
		{
			$secret = $this->cipher->decrypt($encrypted);
		}
		catch (\RuntimeException $e)
		{
			// Undecryptable stored secret (key rotated or data corrupted):
			// fail closed, never fail open.
			return false;
		}

		if (!$this->totp->verify_code($secret, $code, $time))
		{
			return false;
		}

		$this->totp_repository->touch_last_used($user_id, $time);

		return true;
	}

	/**
	 * Verify and consume a backup code at login time ("I lost my phone").
	 *
	 * @param int    $user_id Board user id.
	 * @param string $code    User-supplied backup code.
	 * @param int    $time    Unix timestamp.
	 *
	 * @return bool True when the code was valid and is now spent.
	 */
	public function verify_backup_login(int $user_id, string $code, int $time): bool
	{
		if (!$this->is_enabled($user_id))
		{
			return false;
		}

		if (!$this->backup_codes->verify_and_consume($user_id, $code, $time))
		{
			return false;
		}

		$this->totp_repository->touch_last_used($user_id, $time);

		return true;
	}

	/**
	 * Issue a fresh set of backup codes, invalidating the previous set.
	 * Requires a currently valid TOTP code as confirmation.
	 *
	 * @param int    $user_id Board user id.
	 * @param string $code    Current TOTP code.
	 * @param int    $time    Unix timestamp.
	 *
	 * @return string[]|null New codes on success, null when the TOTP
	 *                       code is wrong or the user is not enrolled.
	 */
	public function regenerate_backup_codes(int $user_id, string $code, int $time): ?array
	{
		if (!$this->verify_totp_login($user_id, $code, $time))
		{
			return null;
		}

		return $this->backup_codes->generate_and_store($user_id);
	}

	/**
	 * Disable TFA for a user: enrolment and all backup codes are removed.
	 *
	 * The caller is responsible for authenticating this request (the UCP
	 * screen requires the account password).
	 *
	 * @param int $user_id Board user id.
	 *
	 * @return void
	 */
	public function disable(int $user_id): void
	{
		$this->totp_repository->remove($user_id);
		$this->backup_codes->delete_all($user_id);
	}

	/**
	 * Decrypt and validate a pending enrolment payload.
	 *
	 * @param string $pending_payload Encrypted payload.
	 * @param int    $time            Unix timestamp (TTL check).
	 *
	 * @return string|null The pending secret, or null when tampered,
	 *                     malformed or older than PENDING_TTL_SECONDS.
	 */
	protected function open_pending(string $pending_payload, int $time): ?string
	{
		try
		{
			$decoded = json_decode($this->cipher->decrypt($pending_payload), true);
		}
		catch (\RuntimeException $e)
		{
			return null;
		}

		if (!is_array($decoded) || !isset($decoded['secret'], $decoded['issued_at']))
		{
			return null;
		}

		if ($time - (int) $decoded['issued_at'] > self::PENDING_TTL_SECONDS)
		{
			return null;
		}

		return (string) $decoded['secret'];
	}
}
