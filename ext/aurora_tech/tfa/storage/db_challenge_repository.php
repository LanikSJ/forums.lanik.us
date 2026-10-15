<?php
/**
 * Aurora Tech TFA — TOTP Two Factor Authentication extension for phpBB.
 *
 * @copyright (c) 2026 Aurora Tech Inc.
 * @license GPL-2.0-only
 */

namespace aurora_tech\tfa\storage;

/**
 * phpBB DBAL implementation of the challenge repository.
 *
 * Covered by integration testing on a live board; unit tests exercise
 * the service layer against the interface.
 */
class db_challenge_repository implements challenge_repository_interface
{
	protected \phpbb\db\driver\driver_interface $db;

	protected string $table_name;

	/**
	 * @param \phpbb\db\driver\driver_interface $db         phpBB database driver.
	 * @param string                            $table_name Fully prefixed challenge table name.
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db, string $table_name)
	{
		$this->db = $db;
		$this->table_name = $table_name;
	}

	/**
	 * {@inheritdoc}
	 */
	public function put(string $session_id, array $challenge): void
	{
		$this->delete($session_id);

		$sql = 'INSERT INTO ' . $this->table_name . ' ' . $this->db->sql_build_array('INSERT', [
			'session_id'	=> $session_id,
			'user_id'		=> (int) $challenge['user_id'],
			'is_admin'		=> (int) $challenge['is_admin'],
			'auto_login'	=> (int) $challenge['auto_login'],
			'viewonline'	=> (int) $challenge['viewonline'],
			'token'			=> (string) $challenge['token'],
			'redirect_url'	=> (string) $challenge['redirect_url'],
			'created_at'	=> (int) $challenge['created_at'],
		]);
		$this->db->sql_query($sql);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get(string $session_id): ?array
	{
		$sql = 'SELECT user_id, is_admin, auto_login, viewonline, token, redirect_url, created_at
			FROM ' . $this->table_name . "
			WHERE session_id = '" . $this->db->sql_escape($session_id) . "'";
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$row)
		{
			return null;
		}

		return [
			'user_id'		=> (int) $row['user_id'],
			'is_admin'		=> (bool) $row['is_admin'],
			'auto_login'	=> (bool) $row['auto_login'],
			'viewonline'	=> (bool) $row['viewonline'],
			'token'			=> (string) $row['token'],
			'redirect_url'	=> (string) $row['redirect_url'],
			'created_at'	=> (int) $row['created_at'],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete(string $session_id): void
	{
		$sql = 'DELETE FROM ' . $this->table_name . "
			WHERE session_id = '" . $this->db->sql_escape($session_id) . "'";
		$this->db->sql_query($sql);
	}

	/**
	 * {@inheritdoc}
	 */
	public function purge_created_before(int $before): void
	{
		$sql = 'DELETE FROM ' . $this->table_name . '
			WHERE created_at < ' . $before;
		$this->db->sql_query($sql);
	}
}
