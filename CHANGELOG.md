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
* [DOCS] Document usage, versions and development
  * `README.md` covers the problem, the compatibility of both lines, installation, configuration, what is covered
    and the known limitations.
  * `DEVELOPERS.md` covers the branches, the runner, the test matrix and the end-to-end fixtures.
* [TASK] Restrict the 10.x line to TYPO3 14 and 15
  * Like `typo3/testing-framework` 10, the 10.x line on `main` supports TYPO3 14.3 and 15; it requires
    `typo3/cms-core` `^14.3 || ^15.0` and tests with `typo3/testing-framework` `^10.0`.
  * `runTests.sh -t` accepts 14 and 15; `testcore13.yml` is a dummy on `main`, its real counterpart lives on the
    branch `9`.
* [TASK] Ignore the `var/` directory
  * TYPO3 compiles its dependency injection container into `var/cache/code/di/` while Composer installs it.
* [BUGFIX] Treat only `FunctionalTestCase::get()` as pass-through code
  * The testing framework's `FunctionalTestCase` was pass-through code as a whole, so a deprecation it triggered
    itself, for instance in `setUp()`, was attributed to the test case class calling `parent::setUp()`. Only
    `$this->get()` passes through now.
* [FEATURE] Attribute deprecations caused by `ext_localconf.php` and `ext_tables.php`
  * `CachedBootstrapFileMapper` maps the cache files the core concatenates from those files back to the file of the
    extension, so a deprecated API called there is reported in every test, not only in the first test of a class,
    which still loads the single files.
  * `ExtTablesMessageCauseResolver` attributes the deprecation of loading `ext_tables.php`, raised by TYPO3 14, to
    the project's `ext_tables.php`.
* [FEATURE] Attribute TCA migrations of project tables and fields
  * `TcaMigrationCauseResolver` reads the tables and fields from the core's TCA migration deprecation and reports it
    when one of them belongs to the project; migrations of third-party TCA alone stay suppressed.
  * Ownership is decided by the TCA files below the `<source>` directories: the file defining a table, or an
    override naming both table and field.
  * The README recommends `phpstan/phpstan-deprecation-rules` for deprecated dependencies of services the core
    instantiates.
