<?php
/**
 * Aurora Tech TFA — TOTP Two Factor Authentication extension for phpBB.
 *
 * @copyright (c) 2026 Aurora Tech Inc.
 * @license GPL-2.0-only
 */

namespace aurora_tech\tfa\service;

/**
 * Encrypts TOTP secrets at rest.
 *
 * Uses libsodium XSalsa20-Poly1305 (crypto_secretbox), bundled with PHP 8,
 * so a database dump alone does not expose usable TOTP secrets. The board
 * key is generated on first use and stored in the phpBB configuration
 * table, which lives on the same host: this protects against leaked SQL
 * dumps and backup files, not against full host compromise — that boundary
 * is documented rather than overstated.
 */
class secret_cipher
{
	/** Name of the phpBB config entry holding the hex-encoded board key. */
	public const CONFIG_KEY_NAME = 'aurora_tfa_encryption_key';

	protected \phpbb\config\config $config;

	/**
	 * @param \phpbb\config\config $config phpBB configuration (key storage).
	 */
	public function __construct(\phpbb\config\config $config)
	{
		$this->config = $config;
	}

	/**
	 * Encrypt a plaintext secret.
	 *
	 * A fresh random nonce is generated per call and prepended to the
	 * ciphertext, so encrypting the same secret twice yields different
	 * output.
	 *
	 * @param string $plaintext Secret to protect.
	 *
	 * @return string Base64-encoded nonce||ciphertext.
	 *
	 * @throws \SodiumException When libsodium rejects the operation.
	 */
	public function encrypt(string $plaintext): string
	{
		$nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
		$ciphertext = sodium_crypto_secretbox($plaintext, $nonce, $this->get_key());

		return base64_encode($nonce . $ciphertext);
	}

	/**
	 * Decrypt a value produced by encrypt().
	 *
	 * @param string $encoded Base64-encoded nonce||ciphertext.
	 *
	 * @return string Decrypted plaintext.
	 *
	 * @throws \RuntimeException When the payload is malformed or fails authentication (tampered or wrong key).
	 * @throws \SodiumException  When libsodium rejects the operation.
	 */
	public function decrypt(string $encoded): string
	{
		$raw = base64_decode($encoded, true);

		if ($raw === false || strlen($raw) <= SODIUM_CRYPTO_SECRETBOX_NONCEBYTES)
		{
			throw new \RuntimeException('aurora_tfa: malformed encrypted secret payload');
		}

		$nonce = substr($raw, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
		$ciphertext = substr($raw, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

		$plaintext = sodium_crypto_secretbox_open($ciphertext, $nonce, $this->get_key());

		if ($plaintext === false)
		{
			throw new \RuntimeException('aurora_tfa: secret authentication failed (tampered data or wrong key)');
		}

		return $plaintext;
	}

	/**
	 * Fetch the board key, generating and persisting it on first use.
	 *
	 * @return string Raw 32-byte key.
	 */
	protected function get_key(): string
	{
		$hex = (string) ($this->config[self::CONFIG_KEY_NAME] ?? '');

		if ($hex === '')
		{
			$hex = bin2hex(random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES));
			$this->config->set(self::CONFIG_KEY_NAME, $hex);
		}

		return (string) hex2bin($hex);
	}
}
