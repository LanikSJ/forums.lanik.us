<?php
/**
 *
 * WebAuthn. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 dmzx, https://www.dmzx-web.net
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace dmzx\webauthn\migrations;

class v1_1_2_credential_hash extends \phpbb\db\migration\migration
{
	private const BATCH_SIZE = 250;

	public static function depends_on(): array
	{
		return ['\dmzx\webauthn\migrations\v1_1_1_challenge_session_binding'];
	}

	public function effectively_installed(): bool
	{
		return $this->db_tools->sql_column_exists($this->table_prefix . 'webauthn_credentials', 'credential_id_b64');
	}

	public function update_schema(): array
	{
		return [
			'add_columns' => [
				$this->table_prefix . 'webauthn_credentials' => [
					'credential_id_b64' => ['TEXT_UNI', ''],
				],
			],
		];
	}

	public function revert_schema(): array
	{
		return [
			'drop_columns' => [
				$this->table_prefix . 'webauthn_credentials' => [
					'credential_id_b64',
				],
			],
		];
	}

	public function update_data(): array
	{
		return [
			['custom', [[$this, 'hash_credential_ids']]],
			['custom', [[$this, 'decode_credential_names']]],
		];
	}

	public function revert_data(): array
	{
		return [
			['custom', [[$this, 'restore_credential_ids']]],
		];
	}

	/**
	 * @param mixed $last_id Last processed original credential_id, from the previous batch
	 * @return mixed
	 */
	public function hash_credential_ids($last_id)
	{
		$table   = $this->table_prefix . 'webauthn_credentials';
		$last_id = is_string($last_id) ? $last_id : '';

		$sql = 'SELECT credential_id
			FROM ' . $table . "
			WHERE credential_id_b64 = ''
				AND credential_id > '" . $this->db->sql_escape($last_id) . "'
			ORDER BY credential_id";
		$result = $this->db->sql_query_limit($sql, self::BATCH_SIZE);
		$rows   = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		if (!$rows)
		{
			return true;
		}

		$cases = [];
		$ids   = [];
		foreach ($rows as $row)
		{
			$raw = base64_decode($row['credential_id'], true);
			if ($raw === false)
			{
				continue;
			}

			$escaped = $this->db->sql_escape($row['credential_id']);
			$cases[] = "WHEN '" . $escaped . "' THEN '" . hash('sha256', $raw) . "'";
			$ids[]   = $row['credential_id'];
		}

		if ($ids)
		{
			$this->db->sql_query('UPDATE ' . $table . '
				SET credential_id_b64 = credential_id,
					credential_id = CASE credential_id ' . implode(' ', $cases) . ' END
				WHERE ' . $this->db->sql_in_set('credential_id', $ids));
		}

		return count($rows) < self::BATCH_SIZE ? true : (string) end($rows)['credential_id'];
	}

	public function restore_credential_ids(): void
	{
		$this->db->sql_query('UPDATE ' . $this->table_prefix . "webauthn_credentials
			SET credential_id = credential_id_b64
			WHERE credential_id_b64 <> ''");
	}

	/**
	 * @param mixed $last_id Last processed credential_id, from the previous batch
	 * @return mixed
	 */
	public function decode_credential_names($last_id)
	{
		$table   = $this->table_prefix . 'webauthn_credentials';
		$last_id = is_string($last_id) ? $last_id : '';

		$sql = 'SELECT credential_id, name
			FROM ' . $table . "
			WHERE credential_id > '" . $this->db->sql_escape($last_id) . "'
			ORDER BY credential_id";
		$result = $this->db->sql_query_limit($sql, self::BATCH_SIZE);
		$rows   = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		if (!$rows)
		{
			return true;
		}

		$cases = [];
		$ids   = [];
		foreach ($rows as $row)
		{
			$decoded = htmlspecialchars_decode($row['name'], ENT_COMPAT);
			if ($decoded === $row['name'])
			{
				continue;
			}

			$cases[] = "WHEN '" . $this->db->sql_escape($row['credential_id']) . "' THEN '" . $this->db->sql_escape($decoded) . "'";
			$ids[]   = $row['credential_id'];
		}

		if ($ids)
		{
			$this->db->sql_query('UPDATE ' . $table . '
				SET name = CASE credential_id ' . implode(' ', $cases) . ' END
				WHERE ' . $this->db->sql_in_set('credential_id', $ids));
		}

		return count($rows) < self::BATCH_SIZE ? true : (string) end($rows)['credential_id'];
	}
}
