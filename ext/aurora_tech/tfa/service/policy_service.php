<?php
/**
 * Aurora Tech TFA — TOTP Two Factor Authentication extension for phpBB.
 *
 * @copyright (c) 2026 Aurora Tech Inc.
 * @license GPL-2.0-only
 */

namespace aurora_tech\tfa\service;

/**
 * Board-wide TFA policy.
 *
 * Three modes, stored in the phpBB configuration:
 * - disabled: the extension is inert at login time;
 * - optional: users may enrol, enforcement only applies to enrolled users;
 * - required for selected groups: members of those groups must enrol —
 *   until they do, their sessions are restricted to the enrolment screen.
 */
class policy_service
{
	/** TFA is switched off board-wide. */
	public const MODE_DISABLED = 0;

	/** Users may enrol; nothing is forced. */
	public const MODE_OPTIONAL = 1;

	/** Members of the configured groups must enrol. */
	public const MODE_REQUIRED_GROUPS = 2;

	/** Config entry holding the mode. */
	public const CONFIG_MODE = 'aurora_tfa_mode';

	/** Config-text entry holding the required group ids (JSON array). */
	public const CONFIG_REQUIRED_GROUPS = 'aurora_tfa_required_groups';

	protected \phpbb\config\config $config;

	/** @var \phpbb\config\db_text */
	protected $config_text;

	/**
	 * @param \phpbb\config\config  $config      phpBB configuration.
	 * @param \phpbb\config\db_text $config_text phpBB long-value configuration.
	 */
	public function __construct(\phpbb\config\config $config, $config_text)
	{
		$this->config = $config;
		$this->config_text = $config_text;
	}

	/**
	 * Current policy mode.
	 *
	 * @return int One of the MODE_* constants.
	 */
	public function get_mode(): int
	{
		return (int) ($this->config[self::CONFIG_MODE] ?? self::MODE_OPTIONAL);
	}

	/**
	 * Whether TFA is switched off board-wide.
	 *
	 * @return bool True when the login flow must not be intercepted.
	 */
	public function is_disabled(): bool
	{
		return $this->get_mode() === self::MODE_DISABLED;
	}

	/**
	 * Group ids whose members must enrol.
	 *
	 * @return int[] Group ids (empty outside MODE_REQUIRED_GROUPS).
	 */
	public function get_required_group_ids(): array
	{
		if ($this->get_mode() !== self::MODE_REQUIRED_GROUPS)
		{
			return [];
		}

		$raw = (string) $this->config_text->get(self::CONFIG_REQUIRED_GROUPS);
		$decoded = json_decode($raw, true);

		if (!is_array($decoded))
		{
			return [];
		}

		return array_values(array_unique(array_map('intval', $decoded)));
	}

	/**
	 * Persist the policy.
	 *
	 * @param int   $mode      One of the MODE_* constants.
	 * @param int[] $group_ids Required group ids (ignored outside MODE_REQUIRED_GROUPS).
	 *
	 * @return void
	 */
	public function set_policy(int $mode, array $group_ids): void
	{
		if (!in_array($mode, [self::MODE_DISABLED, self::MODE_OPTIONAL, self::MODE_REQUIRED_GROUPS], true))
		{
			$mode = self::MODE_OPTIONAL;
		}

		$this->config->set(self::CONFIG_MODE, (string) $mode);
		$this->config_text->set(self::CONFIG_REQUIRED_GROUPS, (string) json_encode(array_values(array_unique(array_map('intval', $group_ids)))));
	}

	/**
	 * Whether enrolment is mandatory for a user, given their group
	 * memberships.
	 *
	 * @param int[] $user_group_ids All group ids of the user.
	 *
	 * @return bool True when the user belongs to at least one required group.
	 */
	public function is_enrolment_required_for_groups(array $user_group_ids): bool
	{
		$required = $this->get_required_group_ids();

		if ($required === [])
		{
			return false;
		}

		return array_intersect(array_map('intval', $user_group_ids), $required) !== [];
	}
}
