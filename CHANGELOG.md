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
* [FEATURE] Report deprecations caused by TYPO3 project code
  * `Extension` is registered in `<extensions>`; with `<source ignoreIndirectDeprecations="true">` it reports a
    deprecation that project code or the test causes through `GeneralUtility::makeInstance()`, dependency
    injection or `$this->get()` of a functional test. Deprecations among core and third-party code stay
    suppressed.
  * `Typo3PassThroughPaths` lists the files of TYPO3 and the testing framework that instantiate on behalf of their
    caller; the parameter `passThroughPaths` adds more.
  * Builds on `calien/phpunit-deprecation-causer` 11, 12 or 13, matching the PHPUnit major of the project, and
    supports TYPO3 13.4, 14.3 and 15.
  * End-to-end tests run every scenario in a TYPO3 functional test instance with fixture extensions loaded through
    `sbuerk/fixture-packages`.
* [TASK] Run the test matrix in GitHub Actions
  * One workflow per TYPO3 major, `testcore13.yml`, `testcore14.yml` and `testcore15.yml`, runs code quality and the
    unit and functional tests with PHPUnit 11, 12 and 13 across the PHP range of that major, plus the lowest
    dependencies, on every pull request.
