# Security Policy

## Supported versions

| Version | Supported |
|---|---|
| 1.0.x | Yes |
| Pre-release (0.x) | No |

The extension targets phpBB 3.3.x on PHP 8.2 or later.

## Reporting a vulnerability

Please report suspected vulnerabilities **privately** by email:

- **bruno.forcier@aurora-tech.systems**

Include the extension version, phpBB and PHP versions, a description of the issue and, when possible, reproduction steps or a proof of concept. Please do not open public issues, forum topics or pull requests for undisclosed vulnerabilities.

You can expect an acknowledgement within **7 days**. Once the issue is confirmed, we will work on a fix, coordinate a release, and credit you in the changelog unless you prefer to remain anonymous.

## Scope notes

Threat-model boundaries that are documented behaviour rather than vulnerabilities:

- TOTP secrets are encrypted at rest (libsodium `crypto_secretbox`); the board key lives in the phpBB configuration table on the same host. This protects leaked SQL dumps and backups. **Full host compromise is out of scope**: an attacker with filesystem and database access can recover the key.
- Backup codes are stored as Argon2id (or bcrypt) hashes and are single-use.
- The login challenge is bound to the pre-login session, carries a random 256-bit token compared with `hash_equals`, and expires after 10 minutes.
- Rate limiting of the verification form relies on the failure audit log and phpBB's session handling; brute force against a 6-digit code within the ±30 s drift window additionally requires a valid password and a live challenge.
