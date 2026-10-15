<?php
/**
 * Aurora Tech TFA — TOTP Two Factor Authentication extension for phpBB.
 *
 * @copyright (c) 2026 Aurora Tech Inc.
 * @license GPL-2.0-only
 */

namespace aurora_tech\tfa\storage;

/**
 * phpBB DBAL implementation of the backup-code repository.
 *
 * Covered by integration testing on a live board (milestone M4 test
 * bench); unit tests exercise the service layer against the interface.
 */
class db_backup_codes_repository implements backup_codes_repository_interface
{
	protected \phpbb\db\driver\driver_interface $db;

	protected string $table_name;

	/**
	 * @param \phpbb\db\driver\driver_interface $db         phpBB database driver.
	 * @param string                            $table_name Fully prefixed backup-codes table name.
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db, string $table_name)
	{
		$this->db = $db;
		$this->table_name = $table_name;
	}

	/**
	 * {@inheritdoc}
	 */
	public function replace_codes(int $user_id, array $code_hashes): void
	{
		$this->delete_all($user_id);

		$rows = [];

		foreach ($code_hashes as $hash)
		{
			$rows[] = [
				'user_id'	=> $user_id,
				'code_hash'	=> (string) $hash,
				'used_at'	=> 0,
			];
		}

		$this->db->sql_multi_insert($this->table_name, $rows);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_active_codes(int $user_id): array
	{
		$sql = 'SELECT code_id, code_hash
			FROM ' . $this->table_name . '
			WHERE user_id = ' . $user_id . '
				AND used_at = 0';
		$result = $this->db->sql_query($sql);

		$codes = [];

		while ($row = $this->db->sql_fetchrow($result))
		{
			$codes[(int) $row['code_id']] = (string) $row['code_hash'];
		}

		$this->db->sql_freeresult($result);

		return $codes;
	}

	/**
	 * {@inheritdoc}
	 */
	public function mark_used(int $code_id, int $time): void
	{
		$sql = 'UPDATE ' . $this->table_name . '
			SET used_at = ' . $time . '
			WHERE code_id = ' . $code_id;
		$this->db->sql_query($sql);
	}

	/**
	 * {@inheritdoc}
	 */
	public function count_active(int $user_id): int
	{
		$sql = 'SELECT COUNT(code_id) AS active_count
			FROM ' . $this->table_name . '
			WHERE user_id = ' . $user_id . '
				AND used_at = 0';
		$result = $this->db->sql_query($sql);
		$count = (int) $this->db->sql_fetchfield('active_count');
		$this->db->sql_freeresult($result);

		return $count;
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete_all(int $user_id): void
	{
		$sql = 'DELETE FROM ' . $this->table_name . '
			WHERE user_id = ' . $user_id;
		$this->db->sql_query($sql);
	}
}
