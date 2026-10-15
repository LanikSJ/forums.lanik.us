# Technical notes — Aurora Tech TFA

Architecture and design decisions, for maintainers and reviewers.

## Layering

```
ucp/ acp/ controller/ helper/      ← phpBB glue, no business logic
        │
service/                          ← unit-tested business logic
  totp_service                      RFC 6238 wrapper (robthree/twofactorauth)
  secret_cipher                     libsodium crypto_secretbox at rest
  backup_codes_service              10 single-use codes, Argon2id/bcrypt
  tfa_manager                       orchestration facade
  login_challenge_service           pending-login challenges
  policy_service                    board-wide policy
        │
storage/                           ← repository interfaces + phpBB DBAL impls
  totp_repository_interface          phpbb_aurora_tfa_totp
  backup_codes_repository_interface  phpbb_aurora_tfa_backup_codes
  challenge_repository_interface     phpbb_aurora_tfa_challenge
```

Services depend on repository **interfaces** only; unit tests run against in-memory doubles (`tests/doubles/`), no live board required. The DBAL implementations are exercised by integration testing on a real board.

## Login interception

Hook: `core.auth_login_session_create_before` (fired after password validation, before session creation). For an enrolled user the listener:

1. stores a challenge server-side — user id, admin flag, autologin, viewonline, redirect — keyed by the **pre-login session id**, with a 256-bit random token and a 10-minute TTL;
2. renders the verification page and ends the request. No phpBB session is created yet.

The verification controller (`POST app.php/tfa/login`) validates the phpBB form key and the challenge token (`hash_equals`), verifies the TOTP code — or a backup code in "lost phone" mode — and only then replays the tail of `\phpbb\auth\auth::login()`: cookie cleanup and old-session deletion for admin re-authentication, `session_create()`, redirect.

Design choice: a dedicated challenge table instead of extra columns on `phpbb_sessions` (the approach of the historical paul999/tfa). The core schema stays untouched and a purge of the extension is clean.

## Cryptography

- **Secrets**: 160-bit base32 (RFC 4226 recommended size), generated server-side by the library's CSPRNG.
- **At rest**: XSalsa20-Poly1305 (`sodium_crypto_secretbox`), random 24-byte nonce per encryption, key auto-generated (32 bytes) into `phpbb_config` on first use. Authenticated encryption: tampered ciphertext fails closed.
- **Verification window**: ±1 time-step (30 s) of clock drift.
- **Backup codes**: 10 characters from a 30-character unambiguous alphabet (no I/L/O/U/0/1), ~49 bits of entropy each, hashed with Argon2id (bcrypt fallback), single-use, all hashes checked on every attempt so timing does not reveal the matching position.
- **Pending enrolment**: between the QR screen and the first-code confirmation, the candidate secret travels inside an encrypted, timestamped payload (15-minute TTL). Nothing touches the database until the first code verifies.

## Failure policy

Everything fails **closed**: undecryptable stored secret, malformed payload, expired challenge, unknown session — all deny access rather than degrade to password-only. The one deliberate exception: when the board policy is *disabled*, the listener steps aside entirely.

## Static analysis scope

PHPStan level 6 covers `ext.php`, `event/`, `migrations/`, `service/`, `storage/` (with minimal phpBB symbol stubs in `tests/stubs/`, never loaded at runtime). The p_master modules, controller and page helper are phpBB-idiom glue (globals, `page_header`/`page_footer`), covered by `php -l` and board testing instead.

## Tables

| Table | Purpose | Cleanup |
|---|---|---|
| `phpbb_aurora_tfa_totp` | one row per enrolled user: encrypted secret, activation and last-use timestamps | removed on purge |
| `phpbb_aurora_tfa_backup_codes` | code hashes, `used_at` marker | removed on purge |
| `phpbb_aurora_tfa_challenge` | pending login challenges, TTL 10 min, purged opportunistically | removed on purge |
