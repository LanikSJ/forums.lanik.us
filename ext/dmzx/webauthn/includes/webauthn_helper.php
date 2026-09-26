<?php
/**
 *
 * WebAuthn. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 dmzx, https://www.dmzx-web.net
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace dmzx\webauthn\includes;

use phpbb\config\config;
use phpbb\db\driver\driver_interface;
use phpbb\request\request;

class webauthn_helper
{
	/** @var string  */
	protected string $credentials_table;

	/** @var string */
	protected string $challenges_table;

	const CHALLENGE_TTL = 300;

	const CBOR_MAX_DEPTH = 32;

	const NAME_MAX_LENGTH = 255;

	public function __construct(
		protected config $config,
		protected driver_interface $db,
		protected request $request,
		string $credentials_table,
		string $challenges_table
	)
	{
		$this->credentials_table = $credentials_table;
		$this->challenges_table  = $challenges_table;
	}

	// =========================================================================
	// Challenge management
	// =========================================================================

	public function generate_challenge(): string
	{
		return $this->base64url_encode(random_bytes(32));
	}

	public function store_challenge(string $challenge, int $user_id, string $type, string $session_id): void
	{
		$this->db->sql_query(
			'DELETE FROM ' . $this->challenges_table .
			' WHERE created < ' . (time() - self::CHALLENGE_TTL)
		);

		$this->db->sql_query(
			'INSERT INTO ' . $this->challenges_table . ' ' .
			$this->db->sql_build_array('INSERT', [
				'challenge'  => $challenge,
				'user_id'    => $user_id,
				'type'       => $type,
				'created'    => time(),
				'session_id' => $session_id,
			])
		);
	}

	public function consume_challenge(string $challenge, string $type): ?array
	{
		$sql = 'SELECT * FROM ' . $this->challenges_table . "\n\t\t\tWHERE challenge = '" . $this->db->sql_escape($challenge) . "'\n\t\t\tAND type = '" . $this->db->sql_escape($type) . "'\n\t\t\tAND created > " . (time() - self::CHALLENGE_TTL);

		$result = $this->db->sql_query($sql);
		$row    = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$row)
		{
			return null;
		}

		$this->db->sql_query(
			"DELETE FROM " . $this->challenges_table .
			" WHERE challenge = '" . $this->db->sql_escape($challenge) . "'"
		);

		return $row;
	}

	// =========================================================================
	// Credential management
	// =========================================================================

	public function store_credential(
		string $credential_id_raw,
		int $user_id,
		string $public_key_pem,
		int $sign_count,
		string $aaguid,
		string $name
	): void
	{
		$this->db->sql_query(
			'INSERT INTO ' . $this->credentials_table . ' ' .
			$this->db->sql_build_array('INSERT', [
				'credential_id'     => $this->credential_key($credential_id_raw),
				'credential_id_b64' => base64_encode($credential_id_raw),
				'user_id'           => $user_id,
				'public_key'        => $public_key_pem,
				'sign_count'        => $sign_count,
				'aaguid'            => $aaguid,
				'name'              => $name,
				'created'           => time(),
				'last_used'         => time(),
			])
		);
	}

	public function credential_key(string $credential_id_raw): string
	{
		return hash('sha256', $credential_id_raw);
	}

	public function normalise_name(string $name): string
	{
		$name = trim(str_replace(["\r", "\n", "\0"], '', $name));

		return utf8_substr($name, 0, self::NAME_MAX_LENGTH);
	}

	public function get_credential_by_id(string $credential_id_raw): ?array
	{
		$sql = "SELECT * FROM " . $this->credentials_table .
			" WHERE credential_id = '" . $this->db->sql_escape($this->credential_key($credential_id_raw)) . "'";

		$result = $this->db->sql_query($sql);
		$row    = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row ?: null;
	}

	public function get_user_credentials(int $user_id): array
	{
		$sql = "SELECT * FROM " . $this->credentials_table .
			" WHERE user_id = " . (int) $user_id .
			" ORDER BY last_used DESC";

		$result = $this->db->sql_query($sql);
		$rows   = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$rows[] = $row;
		}
		$this->db->sql_freeresult($result);

		return $rows;
	}

	public function count_user_credentials(int $user_id): int
	{
		$sql = 'SELECT COUNT(*) AS cnt FROM ' . $this->credentials_table .
			' WHERE user_id = ' . (int) $user_id;

		$result = $this->db->sql_query($sql);
		$count  = (int) $this->db->sql_fetchfield('cnt');
		$this->db->sql_freeresult($result);

		return $count;
	}

	public function update_sign_count(string $credential_key, int $sign_count): void
	{
		$this->db->sql_query(
			'UPDATE ' . $this->credentials_table . ' SET ' .
			$this->db->sql_build_array('UPDATE', [
				'sign_count' => $sign_count,
				'last_used'  => time(),
			]) .
			" WHERE credential_id = '" . $this->db->sql_escape($credential_key) . "'"
		);
	}

	public function delete_credential(string $credential_key, int $user_id): bool
	{
		$this->db->sql_query(
			"DELETE FROM " . $this->credentials_table .
			" WHERE credential_id = '" . $this->db->sql_escape($credential_key) . "'" .
			" AND user_id = " . (int) $user_id
		);

		return $this->db->sql_affectedrows() > 0;
	}

	public function delete_credentials_for_users(array $user_ids): void
	{
		if (empty($user_ids))
		{
			return;
		}

		$this->db->sql_query(
			'DELETE FROM ' . $this->credentials_table .
			' WHERE ' . $this->db->sql_in_set('user_id', array_map('intval', $user_ids))
		);
	}

	public function rename_credential(string $credential_key, int $user_id, string $new_name): void
	{
		$this->db->sql_query(
			'UPDATE ' . $this->credentials_table . ' SET ' .
			$this->db->sql_build_array('UPDATE', ['name' => $new_name]) .
			" WHERE credential_id = '" . $this->db->sql_escape($credential_key) . "'" .
			" AND user_id = " . (int) $user_id
		);
	}

	// =========================================================================
	// Registration verification (WebAuthn §7.1)
	// =========================================================================

	public function verify_registration(array $data, int $user_id, string $session_id, string $required_uv): array
	{
		foreach (['id', 'rawId', 'response', 'type'] as $field)
		{
			if (empty($data[$field]))
			{
				throw new \RuntimeException('WEBAUTHN_ERROR_MISSING_FIELD');
			}
		}

		if ($data['type'] !== 'public-key')
		{
			throw new \RuntimeException('WEBAUTHN_ERROR_INVALID_TYPE');
		}

		$client_data_raw = $this->base64url_decode($data['response']['clientDataJSON']);
		$client_data     = json_decode($client_data_raw, true);

		if (!$client_data)
		{
			throw new \RuntimeException('WEBAUTHN_ERROR_INVALID_CLIENT_DATA');
		}

		if (($client_data['type'] ?? '') !== 'webauthn.create')
		{
			throw new \RuntimeException('WEBAUTHN_ERROR_INVALID_TYPE');
		}

		$challenge_row = $this->consume_challenge($client_data['challenge'] ?? '', 'register');
		if (!$challenge_row || (int) $challenge_row['user_id'] !== $user_id || $challenge_row['session_id'] !== $session_id)
		{
			throw new \RuntimeException('WEBAUTHN_ERROR_INVALID_CHALLENGE');
		}

		if (($client_data['origin'] ?? '') !== $this->get_expected_origin())
		{
			throw new \RuntimeException('WEBAUTHN_ERROR_INVALID_ORIGIN');
		}

		$attestation_raw = $this->base64url_decode($data['response']['attestationObject']);
		$attestation     = $this->cbor_decode($attestation_raw);

		if (!isset($attestation['authData']))
		{
			throw new \RuntimeException('WEBAUTHN_ERROR_MISSING_AUTH_DATA');
		}

		$auth_data_raw = $attestation['authData'];
		$parsed        = $this->parse_authenticator_data($auth_data_raw, true);

		if ($parsed['rp_id_hash'] !== hash('sha256', $this->get_rp_id(), true))
		{
			throw new \RuntimeException('WEBAUTHN_ERROR_INVALID_RP_ID');
		}

		if (!($parsed['flags'] & 0x01))
		{
			throw new \RuntimeException('WEBAUTHN_ERROR_UP_NOT_SET');
		}

		if ($required_uv === 'required' && !($parsed['flags'] & 0x04))
		{
			throw new \RuntimeException('WEBAUTHN_ERROR_UV_REQUIRED');
		}

		if (!($parsed['flags'] & 0x40) || !isset($parsed['credential']))
		{
			throw new \RuntimeException('WEBAUTHN_ERROR_NO_CREDENTIAL_DATA');
		}

		$public_key_pem = $this->cose_key_to_pem($parsed['credential']['public_key']);

		return [
			'credential_id' => $parsed['credential']['id'],
			'public_key'    => $public_key_pem,
			'sign_count'    => $parsed['sign_count'],
			'aaguid'        => $parsed['credential']['aaguid'],
		];
	}

	// =========================================================================
	// Authentication verification (WebAuthn §7.2)
	// =========================================================================

	public function verify_authentication(array $data, string $session_id, string $required_uv): int
	{
		foreach (['id', 'rawId', 'response', 'type'] as $field)
		{
			if (empty($data[$field]))
			{
				throw new \RuntimeException('WEBAUTHN_ERROR_MISSING_FIELD');
			}
		}

		if ($data['type'] !== 'public-key')
		{
			throw new \RuntimeException('WEBAUTHN_ERROR_INVALID_TYPE');
		}

		$credential_id_raw = $this->base64url_decode($data['rawId']);
		$credential        = $this->get_credential_by_id($credential_id_raw);

		if (!$credential)
		{
			throw new \RuntimeException('WEBAUTHN_ERROR_CREDENTIAL_NOT_FOUND');
		}

		$client_data_raw = $this->base64url_decode($data['response']['clientDataJSON']);
		$client_data     = json_decode($client_data_raw, true);

		if (!$client_data)
		{
			throw new \RuntimeException('WEBAUTHN_ERROR_INVALID_CLIENT_DATA');
		}

		if (($client_data['type'] ?? '') !== 'webauthn.get')
		{
			throw new \RuntimeException('WEBAUTHN_ERROR_INVALID_TYPE');
		}

		$challenge_row = $this->consume_challenge($client_data['challenge'] ?? '', 'authenticate');
		if (!$challenge_row || $challenge_row['session_id'] !== $session_id)
		{
			throw new \RuntimeException('WEBAUTHN_ERROR_INVALID_CHALLENGE');
		}

		if (($client_data['origin'] ?? '') !== $this->get_expected_origin())
		{
			throw new \RuntimeException('WEBAUTHN_ERROR_INVALID_ORIGIN');
		}

		$auth_data_raw = $this->base64url_decode($data['response']['authenticatorData']);
		$parsed        = $this->parse_authenticator_data($auth_data_raw, false);

		if ($parsed['rp_id_hash'] !== hash('sha256', $this->get_rp_id(), true))
		{
			throw new \RuntimeException('WEBAUTHN_ERROR_INVALID_RP_ID');
		}

		if (!($parsed['flags'] & 0x01))
		{
			throw new \RuntimeException('WEBAUTHN_ERROR_UP_NOT_SET');
		}

		if ($required_uv === 'required' && !($parsed['flags'] & 0x04))
		{
			throw new \RuntimeException('WEBAUTHN_ERROR_UV_REQUIRED');
		}

		$signature_raw     = $this->base64url_decode($data['response']['signature']);
		$verification_data = $auth_data_raw . hash('sha256', $client_data_raw, true);

		$pubkey = openssl_pkey_get_public($credential['public_key']);
		if ($pubkey === false)
		{
			throw new \RuntimeException('WEBAUTHN_ERROR_INVALID_PUBLIC_KEY');
		}

		$ok = openssl_verify($verification_data, $signature_raw, $pubkey, OPENSSL_ALGO_SHA256);

		if ($ok !== 1)
		{
			throw new \RuntimeException('WEBAUTHN_ERROR_SIGNATURE_INVALID');
		}

		$new_count    = (int) $parsed['sign_count'];
		$stored_count = (int) $credential['sign_count'];

		if (($new_count !== 0 || $stored_count !== 0) && $new_count <= $stored_count)
		{
			throw new \RuntimeException('WEBAUTHN_ERROR_SIGN_COUNT_REPLAY');
		}

		$this->update_sign_count($credential['credential_id'], $new_count);

		$user_type = $this->get_user_type((int) $credential['user_id']);
		if ($user_type === USER_INACTIVE || $user_type === USER_IGNORE)
		{
			throw new \RuntimeException('WEBAUTHN_ERROR_ACCOUNT_INACTIVE');
		}

		return (int) $credential['user_id'];
	}

	private function get_user_type(int $user_id): ?int
	{
		$sql = 'SELECT user_type FROM ' . USERS_TABLE .
			' WHERE user_id = ' . (int) $user_id;

		$result    = $this->db->sql_query($sql);
		$user_type = $this->db->sql_fetchfield('user_type');
		$this->db->sql_freeresult($result);

		return $user_type === false ? null : (int) $user_type;
	}

	// =========================================================================
	// Binary parsing helpers
	// =========================================================================

	private function parse_authenticator_data(string $data, bool $expect_credential): array
	{
		if (strlen($data) < 37)
		{
			throw new \RuntimeException('WEBAUTHN_ERROR_AUTH_DATA_TOO_SHORT');
		}

		$rp_id_hash = substr($data, 0, 32);
		$flags      = ord($data[32]);
		$sign_count = (int) unpack('N', substr($data, 33, 4))[1];

		$result = [
			'rp_id_hash' => $rp_id_hash,
			'flags'      => $flags,
			'sign_count' => $sign_count,
		];

		if ($flags & 0x40)
		{
			if (strlen($data) < 55)
			{
				throw new \RuntimeException('WEBAUTHN_ERROR_AUTH_DATA_TOO_SHORT');
			}

			$offset     = 37;
			$aaguid_raw = substr($data, $offset, 16);
			$offset    += 16;
			$h          = bin2hex($aaguid_raw);
			$aaguid_str = sprintf('%s-%s-%s-%s-%s',
				substr($h, 0, 8), substr($h, 8, 4), substr($h, 12, 4),
				substr($h, 16, 4), substr($h, 20, 12));

			$cred_id_len   = (int) unpack('n', substr($data, $offset, 2))[1];
			$offset       += 2;
			$credential_id = substr($data, $offset, $cred_id_len);
			$offset       += $cred_id_len;
			$cose_key      = $this->cbor_decode(substr($data, $offset));

			$result['credential'] = [
				'aaguid'     => $aaguid_str,
				'id'         => $credential_id,
				'public_key' => $cose_key,
			];
		}
		elseif ($expect_credential)
		{
			throw new \RuntimeException('WEBAUTHN_ERROR_NO_CREDENTIAL_DATA');
		}

		return $result;
	}

	// =========================================================================
	// COSE key → PEM conversion
	// =========================================================================

	private function cose_key_to_pem(array $cose_key): string
	{
		switch ($cose_key[1] ?? null)
		{
			case 2: return $this->ec2_cose_to_pem($cose_key);
			case 3: return $this->rsa_cose_to_pem($cose_key);
			default: throw new \RuntimeException('WEBAUTHN_ERROR_UNSUPPORTED_KEY_TYPE');
		}
	}

	private function ec2_cose_to_pem(array $k): string
	{
		$x = $k[-2] ?? null;
		$y = $k[-3] ?? null;

		if (!is_string($x) || !is_string($y) || strlen($x) !== 32 || strlen($y) !== 32)
		{
			throw new \RuntimeException('WEBAUTHN_ERROR_INVALID_EC_KEY');
		}

		$point    = "\x04" . $x . $y;
		$oid_ec   = "\x06\x07\x2a\x86\x48\xce\x3d\x02\x01";
		$oid_p256 = "\x06\x08\x2a\x86\x48\xce\x3d\x03\x01\x07";
		$algo_seq = "\x30" . $this->der_len(strlen($oid_ec) + strlen($oid_p256)) . $oid_ec . $oid_p256;
		$bit_str  = "\x03" . $this->der_len(strlen($point) + 1) . "\x00" . $point;
		$spki     = "\x30" . $this->der_len(strlen($algo_seq) + strlen($bit_str)) . $algo_seq . $bit_str;

		return $this->der_to_pem($spki);
	}

	private function rsa_cose_to_pem(array $k): string
	{
		$n = $k[-1] ?? null;
		$e = $k[-2] ?? null;

		if (!is_string($n) || !is_string($e))
		{
			throw new \RuntimeException('WEBAUTHN_ERROR_INVALID_RSA_KEY');
		}

		$rsa_seq  = $this->der_integer($n) . $this->der_integer($e);
		$rsa_pub  = "\x30" . $this->der_len(strlen($rsa_seq)) . $rsa_seq;
		$oid_rsa  = "\x06\x09\x2a\x86\x48\x86\xf7\x0d\x01\x01\x01\x05\x00";
		$algo_seq = "\x30" . $this->der_len(strlen($oid_rsa)) . $oid_rsa;
		$bit_str  = "\x03" . $this->der_len(strlen($rsa_pub) + 1) . "\x00" . $rsa_pub;
		$spki     = "\x30" . $this->der_len(strlen($algo_seq) + strlen($bit_str)) . $algo_seq . $bit_str;

		return $this->der_to_pem($spki);
	}

	private function der_integer(string $bytes): string
	{
		$bytes = ltrim($bytes, "\x00") ?: "\x00";
		if (ord($bytes[0]) & 0x80) { $bytes = "\x00" . $bytes; }
		return "\x02" . $this->der_len(strlen($bytes)) . $bytes;
	}

	private function der_len(int $length): string
	{
		if ($length < 0x80)    { return chr($length); }
		if ($length < 0x100)   { return "\x81" . chr($length); }
		if ($length < 0x10000) { return "\x82" . chr($length >> 8) . chr($length & 0xFF); }
		throw new \RuntimeException('DER length overflow');
	}

	private function der_to_pem(string $der): string
	{
		return "-----BEGIN PUBLIC KEY-----\n"
			. chunk_split(base64_encode($der), 64, "\n")
			. "-----END PUBLIC KEY-----\n";
	}

	// =========================================================================
	// Minimal CBOR decoder (RFC 7049)
	// =========================================================================

	public function cbor_decode(string $data): mixed
	{
		$offset = 0;
		return $this->cbor_item($data, $offset, 0);
	}

	private function cbor_item(string $data, int &$offset, int $depth): mixed
	{
		if ($depth > self::CBOR_MAX_DEPTH) { throw new \RuntimeException('CBOR: nesting too deep at ' . $offset); }

		$len = strlen($data);
		if ($offset >= $len) { throw new \RuntimeException('CBOR: unexpected end at ' . $offset); }

		$initial = ord($data[$offset++]);
		$major   = ($initial >> 5) & 0x07;
		$info    = $initial & 0x1F;

		if ($info <= 23)      { $value = $info; }
		elseif ($info === 24) { $this->cbor_need($data, $offset, 1); $value = ord($data[$offset++]); }
		elseif ($info === 25) { $this->cbor_need($data, $offset, 2); $value = (ord($data[$offset]) << 8) | ord($data[$offset + 1]); $offset += 2; }
		elseif ($info === 26) { $this->cbor_need($data, $offset, 4); $value = unpack('N', substr($data, $offset, 4))[1]; $offset += 4; }
		elseif ($info === 27) { $this->cbor_need($data, $offset, 8); $hi = unpack('N', substr($data, $offset, 4))[1]; $lo = unpack('N', substr($data, $offset + 4, 4))[1]; $value = $hi * 4294967296 + $lo; $offset += 8; }
		elseif ($info === 31) { $value = -1; }
		else { throw new \RuntimeException('CBOR: reserved info ' . $info); }

		switch ($major)
		{
			case 0: return (int) $value;
			case 1: return -1 - (int) $value;
			case 2:
				if ($info === 31) { $buf = ''; while ($offset < $len && ord($data[$offset]) !== 0xFF) { $buf .= $this->cbor_item($data, $offset, $depth + 1); } $offset++; return $buf; }
				$this->cbor_need($data, $offset, $value); $result = substr($data, $offset, $value); $offset += $value; return $result;
			case 3:
				if ($info === 31) { $buf = ''; while ($offset < $len && ord($data[$offset]) !== 0xFF) { $buf .= $this->cbor_item($data, $offset, $depth + 1); } $offset++; return $buf; }
				$this->cbor_need($data, $offset, $value); $result = substr($data, $offset, $value); $offset += $value; return $result;
			case 4:
				$arr = [];
				if ($info === 31) { while ($offset < $len && ord($data[$offset]) !== 0xFF) { $arr[] = $this->cbor_item($data, $offset, $depth + 1); } $offset++; }
				else { for ($i = 0; $i < $value; $i++) { $arr[] = $this->cbor_item($data, $offset, $depth + 1); } }
				return $arr;
			case 5:
				$map = [];
				if ($info === 31) { while ($offset < $len && ord($data[$offset]) !== 0xFF) { $k = $this->cbor_item($data, $offset, $depth + 1); $map[$k] = $this->cbor_item($data, $offset, $depth + 1); } $offset++; }
				else { for ($i = 0; $i < $value; $i++) { $k = $this->cbor_item($data, $offset, $depth + 1); $map[$k] = $this->cbor_item($data, $offset, $depth + 1); } }
				return $map;
			case 6: return $this->cbor_item($data, $offset, $depth + 1);
			case 7: switch ($info) { case 20: return false; case 21: return true; default: return null; }
		}

		throw new \RuntimeException('CBOR: unsupported major type ' . $major);
	}

	private function cbor_need(string $data, int $offset, int $need): void
	{
		if ($offset + $need > strlen($data))
		{
			throw new \RuntimeException('CBOR: truncated (need ' . $need . ' at ' . $offset . ')');
		}
	}

	// =========================================================================
	// Base64URL helpers
	// =========================================================================

	public function base64url_encode(string $data): string
	{
		return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
	}

	public function base64url_decode(string $data): string
	{
		$pad  = strlen($data) % 4;
		$data .= $pad ? str_repeat('=', 4 - $pad) : '';
		return base64_decode(strtr($data, '-_', '+/'));
	}

	// =========================================================================
	// URL / configuration helpers
	// =========================================================================

	public function get_rp_id(): string
	{
		$host = $this->config['force_server_vars']
			? (string) $this->config['server_name']
			: ($this->request->server('HTTP_HOST') ?: $this->config['server_name'] ?: 'localhost');

		return (string) preg_replace('/:\d+$/', '', $host);
	}

	public function get_expected_origin(): string
	{
		if ($this->config['force_server_vars'])
		{
			return $this->get_forced_origin();
		}

		$host = $this->request->server('HTTP_HOST')
			?: $this->config['server_name']
			?: 'localhost';

		$https            = $this->request->server('HTTPS', '');
		$forwarded_proto  = $this->request->server('HTTP_X_FORWARDED_PROTO', '');
		$forwarded_port   = $this->request->server('HTTP_X_FORWARDED_PORT', '');

		$secure = ($https !== '' && $https !== 'off') || $forwarded_proto === 'https';

		if ($forwarded_port !== '')
		{
			$port = (int) $forwarded_port;
		}
		elseif ($forwarded_proto !== '')
		{
			$port = $secure ? 443 : 80;
		}
		else
		{
			$port = (int) ($this->request->server('SERVER_PORT') ?: ($secure ? 443 : 80));
		}

		$proto = $secure ? 'https' : 'http';

		if (str_contains($host, ':'))
		{
			return $proto . '://' . $host;
		}

		$omit = ($secure && $port === 443) || (!$secure && $port === 80);
		return $proto . '://' . $host . ($omit ? '' : ':' . $port);
	}

	private function get_forced_origin(): string
	{
		$proto  = (string) ($this->config['server_protocol'] ?: ($this->config['cookie_secure'] ? 'https://' : 'http://'));
		$secure = $proto === 'https://';
		$host   = (string) $this->config['server_name'];
		$port   = (int) $this->config['server_port'];

		$omit = !$port || ($secure && $port === 443) || (!$secure && $port === 80) || str_contains($host, ':');

		return $proto . $host . ($omit ? '' : ':' . $port);
	}
}
