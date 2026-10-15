<?php
/**
 * Aurora Tech TFA — TOTP Two Factor Authentication extension for phpBB.
 *
 * @copyright (c) 2026 Aurora Tech Inc.
 * @license GPL-2.0-only
 */

namespace aurora_tech\tfa\storage;

/**
 * Persistence boundary for pending login challenges.
 *
 * A challenge is created when the password stage succeeds for a
 * TFA-enrolled user, and consumed when the second factor is verified.
 * Keeping challenges in a dedicated table (keyed by the pre-login
 * session) avoids altering the phpBB core sessions schema.
 */
interface challenge_repository_interface
{
	/**
	 * Store a challenge for a session, replacing any previous one.
	 *
	 * @param string $session_id   Pre-login session id.
	 * @param array{user_id: int, is_admin: bool, auto_login: bool, viewonline: bool, token: string, redirect_url: string, created_at: int} $challenge Challenge data.
	 *
	 * @return void
	 */
	public function put(string $session_id, array $challenge): void;

	/**
	 * Fetch the challenge of a session.
	 *
	 * @param string $session_id Pre-login session id.
	 *
	 * @return array{user_id: int, is_admin: bool, auto_login: bool, viewonline: bool, token: string, redirect_url: string, created_at: int}|null
	 *         Challenge data, or null when none exists.
	 */
	public function get(string $session_id): ?array;

	/**
	 * Delete the challenge of a session.
	 *
	 * @param string $session_id Pre-login session id.
	 *
	 * @return void
	 */
	public function delete(string $session_id): void;

	/**
	 * Delete all challenges created before a cut-off time.
	 *
	 * @param int $before Unix timestamp; strictly older rows are removed.
	 *
	 * @return void
	 */
	public function purge_created_before(int $before): void;
}
