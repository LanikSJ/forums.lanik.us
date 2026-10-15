# Contributing to Aurora Tech TFA

Thank you for considering a contribution. This document describes how to propose changes and what the project expects from submitted code.

## Where development happens

Primary development takes place on the Aurora Tech Forgejo instance; the GitHub repository is a public mirror. Pull requests opened on GitHub are welcome and will be reviewed there, then integrated upstream.

## Reporting bugs

Open an issue with:

- your phpBB version, PHP version and the extension version;
- the exact error message (check the phpBB error log and the ACP audit log);
- reproduction steps, starting from a clean state when possible.

Do **not** open public issues for security vulnerabilities — see [SECURITY.md](SECURITY.md).

## Proposing changes

1. Fork the repository and create a feature branch from `main` (`feature/<short-slug>`).
2. Make your changes, following the standards below.
3. Run the checks locally (see "Quality gates").
4. Open a pull request with a clear description of the problem and the approach. Small, focused pull requests are reviewed faster than large ones.

## Code standards

- PHP 8.2+ with PSR-4 autoloading under the `aurora_tech\tfa` namespace and PSR-12 code style (tabs for indentation, phpBB brace style).
- PHPStan level 6 minimum: `vendor/bin/phpstan analyse -c phpstan.neon.dist`.
- PHPUnit tests for any business logic: `vendor/bin/phpunit`. Business logic belongs in `service/` classes behind repository interfaces so it stays unit-testable without a live board; `ucp/`, `acp/`, `controller/` and `helper/` are thin glue.
- No new runtime dependencies without prior discussion in an issue. The dependency footprint (robthree/twofactorauth, bacon/bacon-qr-code) is deliberately minimal.
- DocBlocks in International English on all public methods (`@param`, `@return`, `@throws`), comments explaining the why rather than the how.
- User-facing strings go through the language system — never hard-code them. English (International) is the reference pack; French packs (`fr`, `fr_ca`) should be updated in the same pull request when strings change.

## Quality gates

A pull request is ready for review when all of the following pass:

```
php -l on every changed PHP file
vendor/bin/phpunit
vendor/bin/phpstan analyse -c phpstan.neon.dist
composer validate
```

## Security-sensitive areas

Changes touching `service/secret_cipher.php`, `service/backup_codes_service.php`, `service/login_challenge_service.php` or the login listener/controller receive extra scrutiny: please explain the security reasoning in the pull request description, not just the mechanics.

## Licence

By contributing, you agree that your contribution is licensed under [GPL-2.0-only](LICENSE), the licence of the project.
