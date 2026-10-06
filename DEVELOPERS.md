# Developing the extension

Everything runs in the TYPO3 core-testing containers through `Build/Scripts/runTests.sh`; no PHP or Composer is
needed on the host. `Build/Scripts/runTests.sh -h` lists all suites and options.

## Branches

Like `typo3/testing-framework`, every major of the package supports two TYPO3 majors: `main` is 10.x for TYPO3 14
and 15, `9` is 9.x for TYPO3 13 and 14. Changes reach every branch through pull requests only; they need an
approving review and passing checks, and are merged by rebase. A fix for both lines goes to `main` first and is
backported to `9` in a pull request of its own.

## Installing dependencies

Select the TYPO3 major with `-t` and the PHPUnit major with `-U`, with a PHP version both support:

```shell
Build/Scripts/runTests.sh -p 8.2 -t 13 -U 11 -s composerUpdate
Build/Scripts/runTests.sh -p 8.5 -t 14 -U 13 -s composerUpdate
```

`-s composerUpdateMin` installs the lowest supported releases instead. `composer.json` keeps its spanning
constraints either way. The installed PHPUnit major decides which major of `calien/phpunit-deprecation-causer`
is used.

## Suites

| Suite                                | What it runs                                                         |
| :----------------------------------- | :------------------------------------------------------------------- |
| `-s unit`                            | Unit tests                                                           |
| `-s functional`                      | End-to-end tests, each scenario in a TYPO3 functional test instance  |
| `-s phpstan`                         | PHPStan                                                              |
| `-s cgl` (`-n` for a dry run)        | php-cs-fixer with the TYPO3 core rule set                            |
| `-s lintPhp`                         | PHP syntax check                                                     |
| `-s composerValidate`, `-s checkBom` | Integrity checks                                                     |

Pass options for PHPUnit or PHPStan after `--`, for instance `Build/Scripts/runTests.sh -s functional -- --filter
makeInstance`.

The CI workflows `testcore<major>.yml` run every TYPO3 major of the line with PHPUnit 11, 12 and 13 across its PHP
range, plus the lowest dependencies. A change counts as done when those lanes pass.

## How it works

The mechanism lives in `calien/phpunit-deprecation-causer`. This package adds `Typo3PassThroughPaths`, the files
of TYPO3 and its testing framework that instantiate on behalf of their caller, and `Extension`, which hands them
to the `DeprecationCauserRegistrar` of the base package, merged with the configured `passThroughPaths`.

## End-to-end tests

`tests/EndToEnd/ExtensionTest.php` runs each scenario of `Fixtures/Scenarios/DeprecationScenarios.php` in its own
PHPUnit process, with one of the configurations in `tests/EndToEnd/Fixtures/`. Every scenario boots a TYPO3
functional test instance on SQLite with two fixture extensions:

| Fixture extension          | Role                                                                   |
| :------------------------- | :--------------------------------------------------------------------- |
| `Extensions/first_party/`  | Project code, the only directory in `<source>`                         |
| `Extensions/third_party/`  | A deprecated service and code instantiating it, outside of `<source>`  |
| `Extensions/first_party_bootstrap/` | Project `ext_localconf.php` and `ext_tables.php` calling a deprecated API |
| `Extensions/first_party_tca/` | A project table whose TCA the core migrates |
| `Extensions/first_party_tca_override/` | A project override adding a field the core migrates to a third-party table |
| `Extensions/third_party_tca/` | A third-party table whose TCA the core migrates |

| Configuration                             | Purpose                                                          |
| :---------------------------------------- | :--------------------------------------------------------------- |
| `ignoring-indirect.xml`                   | The extension at work                                            |
| `ignoring-indirect-without-extension.xml` | Reproduces the suppressed deprecations without the extension     |
| `reporting-indirect.xml`                  | Proves nothing is reported twice when PHPUnit reports everything |

The fixture extensions are autoloaded through `sbuerk/fixture-packages` and loaded into the test instance by path.
`DeprecationScenarios` runs one test at a time; the other scenario classes run as a whole, each with its own set of
fixture extensions. `CachedBootstrapScenarios` boots its second test from the cache files the first one created.
