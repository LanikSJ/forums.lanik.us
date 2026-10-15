<?php
/**
 * Aurora Tech TFA — TOTP Two Factor Authentication extension for phpBB.
 *
 * @copyright (c) 2026 Aurora Tech Inc.
 * @license GPL-2.0-only
 */

namespace aurora_tech\tfa\storage;

/**
 * phpBB DBAL implementation of the TOTP enrolment repository.
 *
 * Covered by integration testing on a live board (milestone M4 test
 * bench); unit tests exercise the service layer against the interface.
 */
class db_totp_repository implements totp_repository_interface
{
	protected \phpbb\db\driver\driver_interface $db;

	protected string $table_name;

	/**
	 * @param \phpbb\db\driver\driver_interface $db         phpBB database driver.
	 * @param string                            $table_name Fully prefixed TOTP table name.
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db, string $table_name)
	{
		$this->db = $db;
		$this->table_name = $table_name;
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_enrolled(int $user_id): bool
	{
		return $this->get_enrolment($user_id) !== null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_encrypted_secret(int $user_id): ?string
	{
		$row = $this->get_enrolment($user_id);

		return $row === null ? null : $row['secret_encrypted'];
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_enrolment(int $user_id): ?array
	{
		$sql = 'SELECT user_id, secret_encrypted, activated_at, last_used_at
			FROM ' . $this->table_name . '
			WHERE user_id = ' . $user_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$row)
		{
			return null;
		}

		return [
			'user_id'			=> (int) $row['user_id'],
			'secret_encrypted'	=> (string) $row['secret_encrypted'],
			'activated_at'		=> (int) $row['activated_at'],
			'last_used_at'		=> (int) $row['last_used_at'],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function enrol(int $user_id, string $encrypted_secret, int $time): void
	{
		$this->remove($user_id);

		$sql = 'INSERT INTO ' . $this->table_name . ' ' . $this->db->sql_build_array('INSERT', [
			'user_id'			=> $user_id,
			'secret_encrypted'	=> $encrypted_secret,
			'activated_at'		=> $time,
			'last_used_at'		=> 0,
		]);
		$this->db->sql_query($sql);
	}

	/**
	 * {@inheritdoc}
	 */
	public function touch_last_used(int $user_id, int $time): void
	{
		$sql = 'UPDATE ' . $this->table_name . '
			SET last_used_at = ' . $time . '
			WHERE user_id = ' . $user_id;
		$this->db->sql_query($sql);
	}

	/**
	 * {@inheritdoc}
	 */
	public function remove(int $user_id): void
	{
		$sql = 'DELETE FROM ' . $this->table_name . '
			WHERE user_id = ' . $user_id;
		$this->db->sql_query($sql);
	}
}
