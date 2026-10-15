# Administrator guide — Aurora Tech TFA

This guide covers installation, policy configuration and monitoring.

## Installation

1. Download the release archive and extract it to `ext/aurora_tech/tfa/` so that `ext/aurora_tech/tfa/composer.json` exists. When installing from source instead, run `composer install --no-dev` inside that directory first.
2. In the ACP, go to **Customise → Manage extensions**, find **Two Factor Authentication** and click **Enable**.
3. The extension refuses to enable on PHP older than 8.2 or phpBB older than 3.3 — by design, so it can never fatal on an unsupported runtime.

Enabling the extension creates two tables for enrolments and backup codes, one table for pending login challenges, and a UCP and ACP module. Disabling it (without purging the data) leaves user enrolments intact; purging removes every table, module and configuration entry.

## Policy configuration

**ACP → Extensions → Two Factor Authentication → Settings** offers three modes:

- **Disabled** — the extension is inert at login; nobody is asked for a second factor, even enrolled users.
- **Optional** (default) — users may enable TFA in their User Control Panel; only enrolled users are challenged at login.
- **Required for selected groups** — members of the chosen groups must enrol. Until they do, every page they visit redirects to the enrolment screen (the login, logout and contact-administrator pages stay reachable). Everyone else remains on the optional regime.

A sensible rollout: keep **Optional** for a week or two, announce the feature, then switch to **Required** for the administrator and moderator groups.

## Statistics

**ACP → … → Statistics** shows the number of enrolled users board-wide and per group (members, enrolled, percentage) — useful for tracking adoption before making TFA mandatory for a group.

## Audit log

**ACP → … → Audit log** lists the last 100 TFA events: activations, deactivations, backup-code regenerations, failed login verifications, and policy changes. The same entries appear in the standard phpBB user/admin logs, so your existing log retention applies.

Repeated failed verifications for one account deserve attention: they mean someone has that account's **password** and is stuck at the second factor.

## Recovering a locked-out user

If a user has lost both device and backup codes, verify their identity, then remove their enrolment so they can sign in with password only and re-enrol:

- (Recommended) Have them use the contact-administrator page; after verification, delete their rows from `phpbb_aurora_tfa_totp` and `phpbb_aurora_tfa_backup_codes` (a UCP-based admin reset is on the roadmap).

## Key management

TOTP secrets are encrypted at rest with a board-specific key generated on first use and stored in the phpBB configuration table (`aurora_tfa_encryption_key`). Treat database dumps accordingly: the dump alone does not expose usable secrets, but dump + configuration table does. If you ever need to rotate the key, note that existing enrolments become undecryptable — verification then **fails closed**, and affected users must re-enrol.
