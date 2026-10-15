<?php
/**
 * Aurora Tech TFA — TOTP Two Factor Authentication extension for phpBB.
 *
 * @copyright (c) 2026 Aurora Tech Inc.
 * @license GPL-2.0-only
 */

namespace aurora_tech\tfa\storage;

/**
 * Persistence boundary for backup codes.
 *
 * The service layer depends on this interface only, which keeps business
 * logic unit-testable without a live phpBB database.
 */
interface backup_codes_repository_interface
{
	/**
	 * Replace all backup codes of a user with a freshly generated set.
	 *
	 * @param int      $user_id     Board user id.
	 * @param string[] $code_hashes Password hashes of the new codes.
	 *
	 * @return void
	 */
	public function replace_codes(int $user_id, array $code_hashes): void;

	/**
	 * Fetch the unused backup codes of a user.
	 *
	 * @param int $user_id Board user id.
	 *
	 * @return array<int, string> Map of code row id to code hash.
	 */
	public function get_active_codes(int $user_id): array;

	/**
	 * Mark one backup code as consumed.
	 *
	 * @param int $code_id Code row id.
	 * @param int $time    Unix timestamp of consumption.
	 *
	 * @return void
	 */
	public function mark_used(int $code_id, int $time): void;

	/**
	 * Count the unused backup codes of a user.
	 *
	 * @param int $user_id Board user id.
	 *
	 * @return int Number of codes still available.
	 */
	public function count_active(int $user_id): int;

	/**
	 * Delete all backup codes of a user (used and unused).
	 *
	 * @param int $user_id Board user id.
	 *
	 * @return void
	 */
	public function delete_all(int $user_id): void;
}
