# dmzx/webauthn — Passkey & Biometric Login for phpBB

[![phpBB](https://img.shields.io/badge/phpBB-3.3.x-blue)](https://www.phpbb.com)
[![PHP](https://img.shields.io/badge/PHP-8.0%2B-8892BF)](https://www.php.net)
[![License](https://img.shields.io/badge/license-GPL--2.0-green)](LICENSE)

Adds **passkey / biometric login** to phpBB 3.3.x — the first WebAuthn extension ever built for phpBB.  
Users log in with Face ID, fingerprint, Windows Hello or a FIDO2 security key. No password required.

---

## Features

- 🔑 **One-tap login** on the login page — no username or password needed
- 🪪 **Discoverable credentials** (resident keys) — works without pre-selecting a user
- ⚡ **Challenge pre-fetch** — challenge is loaded at page load so the first tap goes straight to the OS picker (no Android user-gesture timeout)
- 🔄 **Silent retry** — a transient Android Credential Manager hiccup after logout is recovered silently without showing an error
- 🛡️ **Clone detection** via signature counter enforcement (per WebAuthn §7.2: any non-increasing counter is rejected once either side has counted)
- 📱 **Cross-platform** — iOS Face ID / Touch ID, Android biometric (Google Password Manager), Windows Hello, YubiKey
- ⚡ **Zero external dependencies** — pure PHP 8.0 + OpenSSL (no Composer packages)
- 🔒 **ES256 (P-256)** and **RS256** algorithm support
- 🛠️ **Manage passkeys** page in UCP — add, rename, delete

---

## Requirements

| Requirement | Minimum |
|-------------|---------|
| phpBB | 3.3.0 |
| PHP | 8.0 with `openssl` extension |
| HTTPS | **Required** — browsers block WebAuthn on plain HTTP (localhost excepted) |

---

## Install

1. Download the latest release.
2. In the `ext` directory of your phpBB board, create a new directory named `dmzx` (if it does not already exist).
3. Copy the `webauthn` folder to `phpBB/ext/dmzx/` (if done correctly, you'll have the main extension class at (your forum root)/ext/dmzx/webauthn/composer.json).
4. Navigate in the ACP to `Customise -> Manage extensions`.
5. Look for `WebAuthn Passkey & Biometric Login` under the Disabled Extensions list, and click its `Enable` link.

The migrations run automatically and create the two required database tables.

## Uninstall

1. Navigate in the ACP to `Customise -> Extension Management -> Extensions`.
2. Look for `WebAuthn Passkey & Biometric Login` under the Enabled Extensions list, and click its `Disable` link.
3. To permanently uninstall, click `Delete Data` and then delete the `/ext/dmzx/webauthn` folder.

---

## Usage

### Logging in with a passkey

1. Go to the phpBB login page
2. The **🔑 Log in with passkey** button appears briefly in a *Preparing…* spinner while the challenge is pre-loaded (~100–200 ms), then becomes active
3. Tap the button
4. The button shows *Opening passkey…* and the OS passkey picker opens
5. Complete Face ID / fingerprint / security key prompt
6. You are logged in — no password entered

### Registering a passkey

1. Log in with your username and password
2. Go to the UCP sidebar → **Passkeys → Manage passkeys**
3. Optionally enter a label (e.g. "Pixel 9 fingerprint")
4. Click **Register passkey** and complete the biometric prompt
5. The passkey appears in the list and is ready to use

### Managing passkeys

On the **Manage passkeys** page you can add additional passkeys (one per device) and delete any listed passkey.

---

## Architecture

```
dmzx/webauthn/
├── composer.json
├── ext.php                          # is_enableable() — requires PHP 8.0+
├── config/
│   ├── routing.yml                  # 4 POST routes (register/auth × challenge/verify)
│   ├── services.yml                 # DI: controllers, listener, helper
│   └── tables.yml                   # table-name parameters
├── controller/
│   ├── webauthn_controller.php      # 4 JSON endpoints
│   ├── ucp_controller.php           # UCP manage page (register, rename, delete)
│   └── acp_controller.php           # ACP settings page
├── event/
│   └── listener.php                 # language, login-box injection, delete_user cleanup
├── includes/
│   └── webauthn_helper.php          # WebAuthn §7.1/§7.2, CBOR decoder, DER/PEM encoder
├── migrations/
│   ├── v1_0_0_install.php           # tables + UCP module
│   ├── v1_1_0_acp_settings.php      # config + ACP module
│   ├── v1_1_1_challenge_session_binding.php
│   └── v1_1_2_credential_hash.php   # hashed credential key, raw-name policy
├── language/en/
│   ├── webauthn.php                 # all UI strings
│   ├── webauthn_acp.php             # ACP strings
│   ├── info_ucp_webauthn.php        # UCP module title strings
│   └── info_acp_webauthn.php        # ACP module title strings
├── acp/ + adm/style/                # ACP module shim, template and CSS
├── ucp/                             # UCP module shim
└── styles/all/
    ├── template/
    │   ├── event/
    │   │   └── overall_footer_body_after.html  # INCLUDECSS/INCLUDEJS + data-* config (login pages only)
    │   ├── javascript/
    │   │   ├── webauthn_auth.js     # Login: button state machine + challenge pre-fetch + silent retry
    │   │   └── webauthn_register.js # Register flow (navigator.credentials.create) + rename UI
    │   └── ucp_webauthn_body.html   # Manage passkeys page
    └── theme/
        └── webauthn.css
```

### Login button state machine

The button cycles through three states automatically:

| State | Appearance | When |
|-------|-----------|------|
| `init` | 🔄 *Preparing…* (disabled) | Challenge pre-fetch in progress at page load (~100–200 ms) |
| `ready` | 🔑 *Log in with passkey* (active) | Challenge cached, ready to consume instantly |
| `working` | 🔄 *Opening passkey…* (disabled) | After tap — OS picker open + server verify in progress |

On success the spinner stays visible while the page navigates away.  
On `NotAllowedError` (user cancelled / no passkey on device) the error message appears immediately.  
On a transient failure (e.g. Android Credential Manager not ready after logout) the button silently resets to `ready` — no error shown. A second tap then succeeds.

### API endpoints

| Method | Path | Auth | Description |
|--------|------|------|-------------|
| POST | `/ext/dmzx/webauthn/authenticate/challenge` | none | Get request options (discoverable flow) |
| POST | `/ext/dmzx/webauthn/authenticate/verify` | none | Verify assertion → create phpBB session |
| POST | `/ext/dmzx/webauthn/register/challenge` | logged in | Get creation options |
| POST | `/ext/dmzx/webauthn/register/verify` | logged in | Store new credential |

### Database tables

| Table | Purpose |
|-------|---------|
| `{prefix}webauthn_credentials` | One row per registered passkey — `credential_id` (PK, SHA-256 of the raw credential ID), `credential_id_b64` (raw ID, base64, for `excludeCredentials`), `user_id`, `public_key` (PEM), `sign_count`, `aaguid`, `name`, `created`, `last_used` |
| `{prefix}webauthn_challenges` | Pending one-time challenges bound to the requesting session — TTL 5 minutes, pruned automatically on every new challenge request |

---

## Security notes

- **Challenges** are single-use; consuming a challenge deletes it immediately from the database
- **Challenges** are bound to the browser session that requested them, so an assertion cannot be replayed from another session (login-CSRF)
- **Inactive accounts** (`USER_INACTIVE`/`USER_IGNORE`) are refused before a session is created, mirroring phpBB's password login
- **Signature counter** is checked on every authentication: once either the stored or the returned counter is non-zero, a returned counter that is not strictly higher is rejected as a clone signal. Only `0 → 0` (authenticators that never count, e.g. iCloud Keychain) is accepted
- **User verification** is enforced server-side (flag bit `0x04`) when the ACP setting is *Required*
- **Origin** (`clientDataJSON.origin`) and **RP ID** are verified on every request — against the request host by default, or against the board's configured server name/protocol/port when *Force server URL settings* is enabled in the ACP
- **CBOR parsing** has a nesting-depth cap against malformed attestation objects
- **Credentials are scoped to `user_id`** — delete and rename endpoints reject mismatched owners
- **No attestation** required (`attestation: 'none'`) — compatible with all authenticators
- **HTTPS** is enforced by the browser; WebAuthn is unavailable on plain HTTP
- **Biometrics never leave the device** — only a public key and opaque credential ID are stored server-side

---

## Privacy

WebAuthn is by design the most privacy-preserving authentication method available:

- Biometrics (Face ID, fingerprint) are processed entirely on-device and never transmitted
- The stored public key is mathematically useless without the private key (which never leaves the authenticator)
- The AAGUID identifies the *type* of authenticator (e.g. "Apple"), not the serial number
- Each credential is bound to a single origin — cross-site tracking is impossible
- All data is stored on your own server with no third-party dependencies

---

## License

[GNU General Public License v2](http://opensource.org/licenses/GPL-2.0)

## Credits

Developed by [dmzx](https://www.dmzx-web.net)  
WebAuthn specification: [https://www.w3.org/TR/webauthn-2/](https://www.w3.org/TR/webauthn-2/)
