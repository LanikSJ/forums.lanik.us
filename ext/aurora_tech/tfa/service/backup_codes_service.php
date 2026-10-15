<?php
/**
 * Aurora Tech TFA — TOTP Two Factor Authentication extension for phpBB.
 *
 * @copyright (c) 2026 Aurora Tech Inc.
 * @license GPL-2.0-only
 */

namespace aurora_tech\tfa\service;

use aurora_tech\tfa\storage\backup_codes_repository_interface;

/**
 * Single-use backup codes.
 *
 * Codes are shown to the user exactly once at generation time; only
 * password hashes are persisted, so a database leak does not expose
 * usable codes.
 */
class backup_codes_service
{
	/** Number of codes issued per generation. */
	public const CODE_COUNT = 10;

	/** Code length in characters (within the 8–10 range set for v1). */
	public const CODE_LENGTH = 10;

	/**
	 * Code alphabet: uppercase alphanumerics minus lookalikes (I, L, O, U,
	 * 0, 1) so codes survive being read aloud or retyped from paper.
	 */
	protected const ALPHABET = 'ABCDEFGHJKMNPQRSTVWXYZ23456789';

	protected backup_codes_repository_interface $repository;

	/**
	 * @param backup_codes_repository_interface $repository Backup-code persistence.
	 */
	public function __construct(backup_codes_repository_interface $repository)
	{
		$this->repository = $repository;
	}

	/**
	 * Generate a fresh set of backup codes for a user, replacing any
	 * previous set.
	 *
	 * @param int $user_id Board user id.
	 *
	 * @return string[] The plaintext codes, formatted for display
	 *                  (XXXXX-XXXXX). This is the only time they exist in
	 *                  plaintext; only hashes are stored.
	 */
	public function generate_and_store(int $user_id): array
	{
		$codes = [];
		$hashes = [];

		while (count($codes) < self::CODE_COUNT)
		{
			$code = $this->generate_code();

			// Collision odds are negligible (30^10) but uniqueness within
			// a set is a hard guarantee, not a probability.
			if (in_array($code, $codes, true))
			{
				continue;
			}

			$codes[] = $code;
			$hashes[] = $this->hash_code($code);
		}

		$this->repository->replace_codes($user_id, $hashes);

		return array_map([$this, 'format_for_display'], $codes);
	}

	/**
	 * Verify a user-supplied backup code and consume it on success.
	 *
	 * Every stored hash is checked even after a match is found, so the
	 * call's timing does not reveal at which position a valid code sits.
	 *
	 * @param int    $user_id Board user id.
	 * @param string $code    User-supplied code (hyphens, spaces and case are normalised).
	 * @param int    $time    Unix timestamp recorded as consumption time.
	 *
	 * @return bool True when the code was valid and has now been consumed.
	 */
	public function verify_and_consume(int $user_id, string $code, int $time): bool
	{
		$normalised = $this->normalise($code);

		if (strlen($normalised) !== self::CODE_LENGTH)
		{
			return false;
		}

		$matched_id = null;

		foreach ($this->repository->get_active_codes($user_id) as $code_id => $hash)
		{
			if (password_verify($normalised, $hash) && $matched_id === null)
			{
				$matched_id = $code_id;
			}
		}

		if ($matched_id === null)
		{
			return false;
		}

		$this->repository->mark_used($matched_id, $time);

		return true;
	}

	/**
	 * Count the backup codes a user has left.
	 *
	 * @param int $user_id Board user id.
	 *
	 * @return int Number of unused codes.
	 */
	public function count_remaining(int $user_id): int
	{
		return $this->repository->count_active($user_id);
	}

	/**
	 * Remove all backup codes of a user (TFA deactivation).
	 *
	 * @param int $user_id Board user id.
	 *
	 * @return void
	 */
	public function delete_all(int $user_id): void
	{
		$this->repository->delete_all($user_id);
	}

	/**
	 * Generate one random code from the unambiguous alphabet.
	 *
	 * @return string Raw code, CODE_LENGTH characters, no separator.
	 */
	protected function generate_code(): string
	{
		$max = strlen(self::ALPHABET) - 1;
		$code = '';

		for ($i = 0; $i < self::CODE_LENGTH; $i++)
		{
			$code .= self::ALPHABET[random_int(0, $max)];
		}

		return $code;
	}

	/**
	 * Hash a code for storage.
	 *
	 * Argon2id when the PHP build provides it, bcrypt otherwise. Both are
	 * deliberately slow: with 10 codes per user, brute force against a
	 * leaked table stays impractical.
	 *
	 * @param string $code Raw code.
	 *
	 * @return string Password hash.
	 */
	protected function hash_code(string $code): string
	{
		$algorithm = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT;

		return password_hash($code, $algorithm);
	}

	/**
	 * Normalise user input: uppercase, strip separators and whitespace.
	 *
	 * @param string $code User-supplied code.
	 *
	 * @return string Canonical code form.
	 */
	protected function normalise(string $code): string
	{
		return strtoupper((string) preg_replace('/[\s\-]+/', '', $code));
	}

	/**
	 * Format a raw code for display (XXXXX-XXXXX).
	 *
	 * @param string $code Raw code, CODE_LENGTH characters.
	 *
	 * @return string Display form with a hyphen separator.
	 */
	protected function format_for_display(string $code): string
	{
		return substr($code, 0, 5) . '-' . substr($code, 5);
	}
}
