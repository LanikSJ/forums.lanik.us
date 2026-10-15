<?php
/**
 * Aurora Tech TFA — TOTP Two Factor Authentication extension for phpBB.
 *
 * @copyright (c) 2026 Aurora Tech Inc.
 * @license GPL-2.0-only
 */

namespace aurora_tech\tfa\storage;

/**
 * Persistence boundary for per-user TOTP enrolment state.
 */
interface totp_repository_interface
{
	/**
	 * Check whether a user has TOTP enabled.
	 *
	 * @param int $user_id Board user id.
	 *
	 * @return bool True when an enrolment row exists.
	 */
	public function is_enrolled(int $user_id): bool;

	/**
	 * Fetch the encrypted secret of an enrolled user.
	 *
	 * @param int $user_id Board user id.
	 *
	 * @return string|null Encrypted secret payload, or null when not enrolled.
	 */
	public function get_encrypted_secret(int $user_id): ?string;

	/**
	 * Fetch the enrolment row of a user.
	 *
	 * @param int $user_id Board user id.
	 *
	 * @return array{user_id: int, secret_encrypted: string, activated_at: int, last_used_at: int}|null
	 *         Enrolment data, or null when not enrolled.
	 */
	public function get_enrolment(int $user_id): ?array;

	/**
	 * Store the enrolment of a user (insert or overwrite).
	 *
	 * @param int    $user_id          Board user id.
	 * @param string $encrypted_secret Encrypted secret payload.
	 * @param int    $time             Unix timestamp of activation.
	 *
	 * @return void
	 */
	public function enrol(int $user_id, string $encrypted_secret, int $time): void;

	/**
	 * Record a successful TOTP validation.
	 *
	 * @param int $user_id Board user id.
	 * @param int $time    Unix timestamp of the validation.
	 *
	 * @return void
	 */
	public function touch_last_used(int $user_id, int $time): void;

	/**
	 * Remove the enrolment of a user (TFA deactivation).
	 *
	 * @param int $user_id Board user id.
	 *
	 * @return void
	 */
	public function remove(int $user_id): void;
}
