# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.5] — 2026-07-09

### Fixed

- Enable-time guard against incomplete installations: `is_enableable()` now
  refuses to enable the extension when the bundled composer dependencies
  (`vendor/`) are missing, with a clear ACP message, instead of letting the
  board fatal on first use (`Class "RobThree\Auth\TwoFactorAuth" not found`)
  or show a blank page when `display_errors` is off. Thanks to **ssl** and
  **lopoto** (phpbb.com topic 2673163) for the reports — both were caused by
  installing the repository source ZIP, which does not bundle `vendor/`.

### Added

- Packaged release archives (built with `build_release.sh`) that include the
  runtime `vendor/` directory, ready to extract into `ext/`. The repository
  "Download ZIP" source archive remains vendor-less by design.
- Test suite validated on PHP 8.4 (63 tests, 247 assertions).

## [Unreleased]

### Added

- Login-flow interception (M4): server-side challenges bound to the
  pre-login session, TOTP or backup-code verification, admin
  re-authentication support, "I lost my phone" path.
- UCP module (M3): QR enrolment with mandatory first-code check,
  one-time backup-code display and download, password-confirmed
  deactivation, TOTP-confirmed code regeneration.
- ACP module (M5): board-wide policy (disabled / optional / required
  for selected groups with page-level enforcement), adoption
  statistics, audit log.
- Style-neutral responsive stylesheet (M6): inherits the board style's
  colours, prosilver-tested, `prefers-color-scheme` aware.
- Language packs (M7): French and French (Canada, OQLF-aligned).
- Documentation (M8): user guide, administrator guide, technical
  notes, contribution guide, security policy.

- Core TOTP services (M2): `totp_service` (RFC 6238 wrapper around
  robthree/twofactorauth with ±1-step drift window and SVG QR enrolment),
  `secret_cipher` (libsodium encryption of secrets at rest),
  `backup_codes_service` (ten single-use codes, Argon2id/bcrypt hashed).
- Database schema (M2): `aurora_tfa_totp` and `aurora_tfa_backup_codes`
  tables with clean revert path.
- Unit test suite (30 tests) and PHPStan level 6 configuration (M2).
- Extension scaffold: composer manifest, extension base class with runtime
  guards (PHP 8.2+, phpBB 3.3+), empty event listener wiring, baseline
  migration, English (International) language pack skeleton (M1).
