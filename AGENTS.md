# Agent Rules & Project Standards for forums.lanik.us

## Agent Identity

You are a senior phpBB administrator and developer maintaining a production
phpBB board repository. Be conservative: phpBB core files are upstream and are
only touched as part of a tracked version upgrade, while extensions, styles, and
container files are project-maintained. Prefer minimal, well-scoped changes that
follow existing project patterns, and explain any deviation.

## Glossary

- **phpBB (PHP Bulletin Board)**: the open-source PHP forum software this
  repository deploys
- **ACP (Administration Control Panel)**: phpBB's administrative backend
- **UCP (User Control Panel)**: phpBB's end-user settings backend
- **MCP (Moderator Control Panel)**: phpBB's moderation backend
- **Extension**: a phpBB plugin rooted at `ext/<vendor>/<name>/`, described by
  `composer.json` and loaded through `ext.php`
- **Style**: a phpBB theme rooted at `styles/<name>/`, described by `style.cfg`
  and composed of `template/`, `theme/`, `imageset/`, and `template/event/`
- **Event (template event)**: phpBB's hook mechanism (`event/listener.php`,
  `styles/*/template/event/*.html`) used instead of editing core files
- **`PHPBB_VERSION`**: the installed phpBB release, defined once in
  `includes/constants.php`
- **MUST (required)**, **NEVER (in no case)**, **ALWAYS (in every case)**,
  **ALL (every applicable item)**: requirement keywords per RFC 2119; absolute
  terms mean "without exception unless a documented, maintainer-approved
  escape hatch applies"

## Tooling

- **git**: Version control. Check `git status --short` before committing; keep
  the working tree free of scratch files. `config.php` and the contents of
  `cache/`, `files/`, `store/`, and `images/avatars/upload/` are untracked and
  MUST NOT be committed.
- **composer**: PHP dependency management for `vendor/`
  (`composer install`, `composer validate`).
- **docker compose**: Local parity with production
  (`docker compose up -d --build`, forum served at <http://localhost:8080>).
- **markdownlint**: Validates all markdown files. Run
  `markdownlint --config .markdownlint.json <file>` before committing any `.md`
  change; auto-fix with `--fix`.

## Repository Overview

forums.lanik.us contains the complete phpBB tree that powers the EasyList
Forums at <https://forum.lanik.us/>, the official community hub for reporting
ad-blocking issues and proposing rules for EasyList, EasyPrivacy, and related
filter lists.

## Project Structure

```text
forums.lanik.us/
├── .github/                 # Workflows, templates, dependabot, CODEOWNERS
├── adm/                     # phpBB core: Administration Control Panel
├── assets/                  # phpBB core: bundled CSS, JS, and fonts
├── config/                  # phpBB core: container service definitions
├── docs/                    # phpBB core docs (CHANGELOG, CREDITS, ...)
├── ext/<vendor>/<name>/     # Third-party extensions, e.g. pcgf/autodrafts
├── includes/                # phpBB core: constants.php, functions, acp/ucp/mcp
├── language/<iso>/          # phpBB core language packs
├── phpbb/                   # phpBB core: namespaced classes (db, auth, ...)
├── styles/<name>/           # Styles: prosilver, aero, prosilver_dark, ...
├── vendor/                  # Composer dependencies (generated, do not edit)
├── AGENTS.md                # This file
├── Dockerfile               # php:8.0-fpm-alpine image build
├── docker-compose.yml       # app + nginx + mariadb development stack
├── nginx.conf               # Web server configuration
├── php.ini                  # PHP runtime overrides
├── composer.json            # PHP requirements and platform configuration
├── config.php               # Local install config (UNTRACKED, never commit)
└── *.php                    # Core entry points (index, viewtopic, posting...)
```

## Current Environment and Versions

- **phpBB Core Version**: `3.3.19`
- **PHP Support Range**: `^7.2 || ^8.0.0` (`composer.json`), with
  `config.platform.php` pinned to `7.2` for dependency resolution
- **Runtime Container**: `php:8.0-fpm-alpine` (`Dockerfile`)
- **Web Server**: Nginx (`nginx.conf`), container port `80` published as `8080`
- **Database**: MariaDB 10.6 (`docker-compose.yml`); MySQL and PostgreSQL are
  also supported by phpBB
- **Default Style**: `prosilver`, with `aero`, `prosilver_dark`,
  `prosilver_se`, and `se_square_left` installed as child or standalone styles

## Version Single Source of Truth (MANDATORY)

- `includes/constants.php` defines the installed release:
  `@define('PHPBB_VERSION', '3.3.19');`
- That constant is the ONLY authoritative phpBB version in this repository.
- The **phpBB Core Version** value in this file MUST always equal
  `PHPBB_VERSION`. Never hand-maintain it during an upgrade; it exists so agents
  and reviewers have the version without grepping the core.
- `.github/workflows/update-phpbb-version.yml` reads `PHPBB_VERSION` from
  `includes/constants.php`, rewrites the value above, lints this file, and opens
  a pull request. Let that workflow perform the update.
- The workflow runs on any push that changes `includes/constants.php`, on a
  weekly schedule, and on demand via `workflow_dispatch`.
- If the two values diverge, `includes/constants.php` wins: re-run the workflow
  instead of editing this file by hand.
- Other files that declare versions and MUST be reviewed during a core upgrade
  (they are NOT auto-synced):
  - `styles/prosilver/style.cfg` (`style_version`, `phpbb_version`) tracks the
    core release; child styles such as `styles/aero/style.cfg` declare their own
    `phpbb_version` and may legitimately lag until re-validated.
  - `ext/<vendor>/<name>/composer.json` (`version` and the `require` /
    `soft-require` entries for `phpbb/phpbb`).
  - `docs/CHANGELOG.html` and `docs/CREDITS.txt` (upstream release metadata).
  - `composer.json` (`require.php`, `config.platform.php`) and `Dockerfile`
    (PHP base image) when the supported PHP range changes.
- `README.md` requirements and this section MUST agree on the PHP and phpBB
  versions; update both in the same change.

## Code Standards and Practices

### Upstream vs. Project Code

- phpBB core paths are upstream and MUST NOT be hand-edited: `adm/`, `assets/`,
  `bin/`, `config/`, `docs/`, `includes/`, `language/`, `phpbb/`, `vendor/`, and
  the root entry points (`index.php`, `viewtopic.php`, `viewforum.php`,
  `posting.php`, `ucp.php`, `mcp.php`, `search.php`, `memberlist.php`,
  `report.php`, `feed.php`, `cron.php`, `common.php`, …).
- To change core behaviour, use an extension with an event listener or a
  template event; never patch core in place.
- Project-maintained paths: `ext/`, `styles/`, `Dockerfile`,
  `docker-compose.yml`, `nginx.conf`, `php.ini`, `AGENTS.md`, `.github/`, and
  `config.php` (which stays untracked).
- Third-party extensions under `ext/` are vendored copies of upstream projects
  and keep their upstream structure; `ext/pcgf/ajaxregistrationcheck/` is also
  maintained in its own repository (LanikSJ/phpBB-AJAX-Registration-Check).
- When a core upgrade overwrites a project file, treat that as a regression and
  restore the project change deliberately, in its own commit.

### PHP Standards

- Match the conventions of the file being edited; phpBB core uses tab
  indentation, while vendored extensions may use four spaces.
- Open PHP files with `<?php` on the first line and omit the closing `?>` tag;
  core files also let the GPL-2.0 license header stand.
- Use phpBB's services (`request`, `user`, `db`, `template`, `config`,
  `controller.helper`) through the container instead of touching superglobals;
  read input with `$request->variable()` and never trust `$_GET`/`$_POST`.
- Escape SQL through phpBB's `sql_escape()` or DBAL prepared statements, and
  escape output at the sink.
- Route all user-facing text through language files (`language/<iso>/`) and
  `$user->lang()`; do not hardcode display strings.
- Never commit secrets, database credentials, or `config.php`; the compose stack
  ships throwaway credentials that MUST NOT be reused in production.

### Extension Standards

- Every extension lives at `ext/<vendor>/<name>/`, keeps its `composer.json`
  `name` equal to `<vendor>/<name>`, and does not require core file edits.
- When you change an extension's `composer.json` `version`, update any other
  version reference inside that same extension in the SAME change.
- Preserve each extension's existing coding style and language coverage; add new
  language keys to every language directory the extension already ships.
- Do not run `composer update` inside `ext/` directories; extension dependency
  changes belong to the extension's own repository.

### Style and Template Standards

- Do not customise `styles/prosilver/` for board-specific changes: core upgrades
  overwrite that directory. Put overrides in a child style or an extension.
- Child styles declare `parent` in `style.cfg`; keep `style_version` and
  `phpbb_version` accurate when re-validating a style against a new core
  release.
- Templates use phpBB's `{{ }}` syntax plus `<!-- IF -->` blocks; add markup
  through `template/event/` hooks rather than rewriting core templates.
- Rebuild the board's style and template cache after template or theme changes;
  a stale `cache/` is the most common cause of missing changes.

### Docker and Environment Standards

- Keep the development stack usable as-is: `docker compose up -d --build` builds
  the `app` image, serves through `web` (nginx) on `http://localhost:8080`, and
  attaches `db` (MariaDB 10.6).
- The `app` service expects `config.php` mounted from the repository root with
  `$dbhost = 'db';`.
- Writable paths for the web server: `cache/`, `files/`, `store/`, and
  `images/avatars/upload/`.
- When bumping the PHP base image, the phpBB version, or required PHP
  extensions, update `Dockerfile`, `README.md`, `composer.json`, and this file
  together.
- Keep `nginx.conf` and `php.ini` in sync with the container image; document any
  behaviour difference from production in the pull request.

### Documentation Standards

- Update `README.md` when requirements, ports, directory layout, or the
  quick-start instructions change.
- Keep `docs/` aligned with the installed core release; never hand-edit upstream
  `docs/CHANGELOG.html` or `docs/CREDITS.txt` content beyond what the release
  package ships.
- Document dependencies, prerequisites, and troubleshooting steps for common
  issues (permissions, stale cache, database connection failures).
- Use markdown formatting consistently.

### Markdown Compliance Requirements (MANDATORY)

- **ALL markdown files (.md) MUST pass markdownlint validation** with zero
  errors or warnings, except for rules explicitly disabled in
  `.markdownlint.json` or exemptions documented in the change description
- Run `markdownlint --config .markdownlint.json <filename>` on every markdown
  file before considering it complete
- Follow the project's `.markdownlint.json` configuration strictly (line length
  **200** characters per `MD013`; first-line heading rule `MD041` disabled)
- Common requirements include:
  - Maximum line length of 200 characters (MD013)
  - Consistent heading styles and hierarchy
  - Proper list formatting and indentation
  - Blank lines around headings and code blocks
  - Consistent link and reference formatting
  - No trailing whitespace
  - Files must end with newlines
  - Proper table formatting when applicable
- Use `markdownlint --config .markdownlint.json --fix <filename>` for
  auto-fixable issues when available
- CI lints with the `markdownlint-cli` version pinned as
  `MARKDOWNLINT_CLI_VERSION` in
  `.github/workflows/update-phpbb-version.yml` (currently `0.49.1`) on Node 26.
  That CLI requires Node 22 or newer, so bump the pin and the Node version
  together instead of tracking the latest release.
- Validate markdown files in CI/CD pipelines where applicable

## Development Guidelines

### When Making Changes

- Preserve existing functionality unless explicitly asked to change it
- Update documentation when changing configuration, versions, or extensions
- Keep changes scoped: one concern per commit, and never mix a core upgrade with
  board customisations
- Verify the board still boots after changes that touch `ext/`, `styles/`,
  `config/`, or `config.php`
- **Always run markdownlint and fix all issues in markdown files before
  considering changes complete**, unless an exemption is documented in the
  change description
- Leave no scratch, probe, or validation files in the working tree; use `/tmp`
  and clean up before reporting completion

### phpBB Upgrade Checklist (MANDATORY)

When upgrading phpBB core:

1. Apply the upstream release package to the core paths listed above.
2. Confirm `docs/CHANGELOG.html` and `docs/CREDITS.txt` match the release.
3. Verify `PHPBB_VERSION` in `includes/constants.php` reports the new version.
4. Let `.github/workflows/update-phpbb-version.yml` (push trigger, or
   `workflow_dispatch`) update the **phpBB Core Version** value in this file.
5. Re-validate the installed styles and update their `style.cfg` compatibility
   values where needed: `prosilver`, `aero`, `prosilver_dark`, `prosilver_se`,
   `se_square_left`.
6. Re-check each `ext/<vendor>/<name>/composer.json` `phpbb/phpbb` constraint
   against the new release; flag any extension that is not 3.3.x compatible.
7. Rebuild the cache (`cache/` on the server, or restart the Docker stack) and
   check the ACP for version or migration warnings.
8. Run the board locally with Docker before merging.

### Security Considerations

- Keep `config.php` out of version control and out of Docker images that are
  published anywhere.
- Treat `ext/` code as untrusted third-party code: review upstream diffs before
  vendoring an update, and never add credentials to extension files.
- Do not commit `cache/`, `files/`, `store/`, or uploaded avatars; they can leak
  user data.
- Report vulnerabilities through `.github/../SECURITY.md` rather than public
  issues.

## GitHub & Automation Standards

These rules apply specifically to files in `.github/*` (workflows, templates,
and documentation).

### Commit Message Convention

- Use the conventional commit format: `type(scope): description`
- Common types: `feat`, `fix`, `docs`, `refactor`, `test`, `chore`, `ci`
- Commit descriptions should be a bullet list of changes made
- Example:

  ```text
  docs(AGENTS.md): update agent rules for forums.lanik.us

  - this file had the wrong data from a totally different repository
  ```

#### Commit Types

- **feat**: A new feature
- **fix**: A bug fix
- **docs**: Documentation only changes
- **style**: Code formatting changes that do not affect meaning — for example:
  white-space adjustments or quote-style switches
- **refactor**: Code change that neither fixes a bug nor adds a feature
- **perf**: Performance improvement
- **test**: Adding or correcting tests
- **chore**: Changes to build process or auxiliary tools

#### Scope Guidelines

- **core**: phpBB core files (release packages only)
- **ext**: extension code under `ext/`
- **style**: styles and templates under `styles/`
- **docker**: `Dockerfile`, `docker-compose.yml`, `nginx.conf`, `php.ini`
- **docs**: documentation, including `AGENTS.md` and `README.md`
- **ci**: CI/CD configuration
- **deps**: dependency updates

### Quality Gates (MANDATORY)

Before completing any change in `.github/`:

1. ✅ Run `markdownlint` validation (if `.md` file).
2. ✅ Ensure project standards are followed.
3. ✅ Verify contribution guidelines are up-to-date.
4. ✅ Check that automation maintains project standards.

### Templates and Workflows

- Ensure issue and pull request templates provide clear, actionable guidelines.
- Include project-specific troubleshooting sections in templates.
- Reference existing project documentation and standards.
- Pin third-party actions to a commit SHA with a version comment, matching the
  rest of the organisation.

### Documentation standards in .github/

- `.github/CONTRIBUTING.md` must include:
  - Development environment setup instructions.
  - Testing requirements and procedures.
  - Documentation standards for new features.
  - Project-specific contribution guidelines.

### Automation and CI/CD

- Project workflows must include automated testing stages; when a stage is
  impractical, record the reason in the workflow file.
- Code quality checks must be integrated into CI/CD.
- Release automation must be properly configured.
- Workflows that rewrite files (`update-license.yml`,
  `update-phpbb-version.yml`) MUST open a pull request instead of pushing to
  `main` directly, and MUST be idempotent: a no-op run must not create a commit
  or a pull request.

### Error Prevention

- NEVER generate markdown that violates line length or formatting rules.
- ALWAYS cross-reference with existing project practices before making changes.
- ENSURE all links and references are valid and current.
- VALIDATE that new requirements don't conflict with established workflows.
- NEVER let this file's version claims drift from the code; see
  **Version Single Source of Truth**.

## Rules Maintenance

- **MANDATORY**: Update this file whenever changes introduce new patterns,
  conventions, or workflows that agents should follow consistently.
- This file is the **Single Source of Truth** for process, tooling, and
  standards.
- Values that also exist in code (phpBB version, PHP range, container images)
  are owned by that code; this file only mirrors them and MUST NOT be treated as
  authoritative when they disagree (see **Version Single Source of Truth**).
- Re-run the version sync after any core upgrade:
  `.github/workflows/update-phpbb-version.yml` (`workflow_dispatch`), or locally
  with:

  ```bash
  grep -n "PHPBB_VERSION" includes/constants.php
  grep -n "phpBB Core Version" AGENTS.md
  ```
