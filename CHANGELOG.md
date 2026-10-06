# Changelog

### Unreleased

* [TASK] Add containerized build and code quality tooling
  * `Build/Scripts/runTests.sh`, modelled on the TYPO3 core runner, runs every suite in the
    `ghcr.io/typo3/core-testing-php*` images: `unit`, `functional` on SQLite, `cgl`, `phpstan`, `lintPhp`,
    `checkBom` and the composer suites.
  * `-s composerUpdate -t <13|14|15> -U <11|12|13>` installs the selected TYPO3 and PHPUnit majors without changing
    `composer.json`.
  * Code style follows the TYPO3 core php-cs-fixer rule set, PHPStan runs on level `max` with the PHPUnit and strict
    rules. The package is licensed under GPL-2.0-or-later.
