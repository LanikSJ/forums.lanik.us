<?php
/**
 * Aurora Tech TFA — TOTP Two Factor Authentication extension for phpBB.
 *
 * @copyright (c) 2026 Aurora Tech Inc.
 * @license GPL-2.0-only
 */

namespace aurora_tech\tfa\service;

use aurora_tech\tfa\storage\challenge_repository_interface;

/**
 * Login challenge lifecycle.
 *
 * When the password stage succeeds for a TFA-enrolled user, no session
 * is created. Instead a short-lived challenge is stored server-side,
 * bound to the pre-login session and to a random token echoed through
 * the verification form. The second-factor form therefore cannot be
 * replayed, forged for another session, or used after its lifetime.
 */
class login_challenge_service
{
	/** Challenge lifetime in seconds. */
	public const TTL_SECONDS = 600;

	protected challenge_repository_interface $repository;

	/**
	 * @param challenge_repository_interface $repository Challenge persistence.
	 */
	public function __construct(challenge_repository_interface $repository)
	{
		$this->repository = $repository;
	}

	/**
	 * Issue a challenge for a session, replacing any previous one.
	 *
	 * Expired challenges (any session) are purged opportunistically.
	 *
	 * @param string $session_id   Pre-login session id.
	 * @param int    $user_id      User who passed the password stage.
	 * @param bool   $is_admin     ACP re-authentication flow.
	 * @param bool   $auto_login   "Remember me" was requested.
	 * @param bool   $viewonline   Session should be visible online.
	 * @param string $redirect_url Post-login redirect target (board-relative).
	 * @param int    $time         Unix timestamp.
	 *
	 * @return string The challenge token to embed in the verification form.
	 */
	public function issue(string $session_id, int $user_id, bool $is_admin, bool $auto_login, bool $viewonline, string $redirect_url, int $time): string
	{
		$this->repository->purge_created_before($time - self::TTL_SECONDS);

		$token = bin2hex(random_bytes(32));

		$this->repository->put($session_id, [
			'user_id'		=> $user_id,
			'is_admin'		=> $is_admin,
			'auto_login'	=> $auto_login,
			'viewonline'	=> $viewonline,
			'token'			=> $token,
			'redirect_url'	=> $redirect_url,
			'created_at'	=> $time,
		]);

		return $token;
	}

	/**
	 * Validate a submitted token against the challenge of a session.
	 *
	 * @param string $session_id Pre-login session id.
	 * @param string $token      Token echoed by the verification form.
	 * @param int    $time       Unix timestamp.
	 *
	 * @return array{user_id: int, is_admin: bool, auto_login: bool, viewonline: bool, token: string, redirect_url: string, created_at: int}|null
	 *         The challenge when the token matches and is fresh, null otherwise.
	 */
	public function validate(string $session_id, string $token, int $time): ?array
	{
		$challenge = $this->repository->get($session_id);

		if ($challenge === null || $token === '')
		{
			return null;
		}

		if (!hash_equals($challenge['token'], $token))
		{
			return null;
		}

		if ($time - $challenge['created_at'] > self::TTL_SECONDS)
		{
			$this->repository->delete($session_id);

			return null;
		}

		return $challenge;
	}

	/**
	 * Consume the challenge of a session (successful verification).
	 *
	 * @param string $session_id Pre-login session id.
	 *
	 * @return void
	 */
	public function consume(string $session_id): void
	{
		$this->repository->delete($session_id);
	}
}
