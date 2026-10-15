# Aurora Tech TFA

TOTP Two Factor Authentication (RFC 6238) extension for phpBB 3.3.x, built for modern PHP 8 boards.

> **Status: stable.** v1.0.0 — validated end-to-end on a live phpBB 3.3.17 / PHP 8.3 board.

## Purpose

The phpBB 3.3.x ecosystem has no actively maintained Two Factor Authentication extension compatible with PHP 8. Historical options either depend on abandoned libraries locked to PHP 5.4–7.x or rely on third-party SaaS endpoints. Aurora Tech TFA fills that gap with a clean, self-contained TOTP implementation — no external service calls, everything runs on your own board.

## Features

- Time-based one-time passwords (RFC 6238) compatible with Aegis, Google Authenticator, Authy, 1Password, Bitwarden and any standard TOTP client
- QR-code enrolment (SVG, no Imagick/GD required) with mandatory first-code verification — users cannot lock themselves out by activating blind
- Ten single-use backup codes with a "I lost my phone" path at login, downloadable as a text file, shown exactly once
- Second factor enforced *between* password check and session creation — no session exists until the code verifies; ACP re-authentication is covered too
- Per-group policy from the ACP: disabled, optional, or required for selected groups (with page-level enforcement until enrolment)
- Statistics (board-wide and per-group adoption) and an audit log (activations, deactivations, regenerations, failed verifications)
- Secrets encrypted at rest (libsodium), backup codes stored as Argon2id/bcrypt hashes only
- Language packs: English (International), French, French (Canada, OQLF-aligned), Portuguese, Portuguese (Brazil), Spanish (formal and informal)

## Requirements

- phpBB **3.3.0** or later (developed and tested against 3.3.17)
- PHP **8.2** or later
- PHP `iconv` extension (bundled with standard PHP builds)

## Installation

1. Download the latest **packaged release archive** (it bundles the required
   libraries in `vendor/`). ⚠️ Do **not** install the repository source ZIP
   ("Download ZIP" / "Code" button): it does not contain `vendor/` and the
   extension will refuse to enable.
2. Extract it to `ext/aurora_tech/tfa/` inside your phpBB root so that `ext/aurora_tech/tfa/composer.json` exists.
3. Only if you are installing from source rather than a release archive: run `composer install --no-dev` inside `ext/aurora_tech/tfa/`.
4. In the ACP, go to **Customise → Manage extensions**, locate **Two Factor Authentication** and click **Enable**.

## Usage

- [User guide](docs/user-guide.md) — enabling TFA, signing in, backup codes, lost-device recovery
- [Administrator guide](docs/admin-guide.md) — installation, policy modes, statistics, audit, locked-out users, key management
- [Technical notes](docs/technical-notes.md) — architecture, login interception, cryptography, failure policy

## Repository

Public home: [worktree.ca/bforcier/phpbb-tfa](https://worktree.ca/bforcier/phpbb-tfa) — issues and pull requests welcome there.

## Contributing and security

- [CONTRIBUTING.md](CONTRIBUTING.md) — standards, quality gates, how to propose changes
- [SECURITY.md](SECURITY.md) — private vulnerability reporting

## Acknowledgements

- [paul999/phpbb_2fa](https://github.com/paul999/phpbb_2fa) — historical inspiration for TFA on phpBB
- [Bastien59960/reactions](https://github.com/Bastien59960/reactions) — reference for modern, well-structured phpBB extension code
- [robthree/twofactorauth](https://github.com/RobThree/TwoFactorAuth) — TOTP library under the hood
- [bacon/bacon-qr-code](https://github.com/Bacon/BaconQrCode) — pure-PHP QR code generation

## License

[GPL-2.0-only](LICENSE) — © 2026 Aurora Tech Inc.
