[![Latest Stable Version](https://poser.pugx.org/calien/typo3-deprecation-causer/v/stable.svg?style=for-the-badge)](https://packagist.org/packages/calien/typo3-deprecation-causer)
[![License](https://poser.pugx.org/calien/typo3-deprecation-causer/license?style=for-the-badge)](https://packagist.org/packages/calien/typo3-deprecation-causer)
[![TYPO3 15](https://img.shields.io/badge/TYPO3-15-orange.svg?style=for-the-badge)](https://get.typo3.org/version/15)
[![TYPO3 14.3](https://img.shields.io/badge/TYPO3-14.3-green.svg?style=for-the-badge)](https://get.typo3.org/version/14.3)
[![TYPO3 13.4](https://img.shields.io/badge/TYPO3-13.4-green.svg?style=for-the-badge)](https://get.typo3.org/version/13.4)
[![Total Downloads](https://poser.pugx.org/calien/typo3-deprecation-causer/downloads.svg?style=for-the-badge)](https://packagist.org/packages/calien/typo3-deprecation-causer)
[![Monthly Downloads](https://poser.pugx.org/calien/typo3-deprecation-causer/d/monthly?style=for-the-badge)](https://packagist.org/packages/calien/typo3-deprecation-causer)

# PHPUnit extension `calien/typo3-deprecation-causer`

|                 | URL                                                             |
|-----------------|-----------------------------------------------------------------|
| **Repository:** | https://github.com/calien666/typo3-deprecation-causer           |
| **Packagist:**  | https://packagist.org/packages/calien/typo3-deprecation-causer  |
| **ISSUES:**     | https://github.com/calien666/typo3-deprecation-causer/issues/   |
| **RELEASES:**   | https://github.com/calien666/typo3-deprecation-causer/releases/ |

## Description

A PHPUnit extension for TYPO3 projects and extensions that keeps `failOnDeprecation="true"` meaningful when
`ignoreIndirectDeprecations="true"` is set: deprecations your own code causes through
`GeneralUtility::makeInstance()`, dependency injection or `$this->get()` of a functional test are reported again,
while deprecations that TYPO3 and other third-party code trigger among themselves stay suppressed.

It is the TYPO3 integration of
[`calien/phpunit-deprecation-causer`](https://github.com/calien666/phpunit-deprecation-causer), which explains the
mechanism in detail.

## The problem

With `<source ignoreIndirectDeprecations="true">`, PHPUnit suppresses a deprecation when both the file that
triggered it and the file that called into it are third-party code. A TYPO3 functional test that boots the
installation needs that setting, otherwise core deprecations unrelated to the project fail every test. But it
also hides what the project causes:

```php
// Reported: project code calls the deprecated constructor.
new DeprecatedService();

// Suppressed: GeneralUtility calls the deprecated constructor on behalf of the project.
GeneralUtility::makeInstance(DeprecatedService::class);

// Suppressed: the container builds a deprecated dependency of a project service.
$this->get(ProjectServiceWithDeprecatedDependency::class);
```

The extension knows the TYPO3 and testing framework files that instantiate on behalf of their caller and lets
PHPUnit judge the first frame behind them instead. If that frame is first-party code (inside `<source>`) or the
test itself, PHPUnit reports the deprecation.

## Compatibility

Like `typo3/testing-framework`, every major of the package supports two TYPO3 majors, and carries the number of
the testing framework major it goes with.

| Branch | State       | Composer Package Name           | Version    | TYPO3     | PHPUnit    | PHP                                     |
|--------|-------------|---------------------------------|------------|-----------|------------|-----------------------------------------|
| main   | development | calien/typo3-deprecation-causer | 10.0.x-dev | v14 + v15 | 11, 12, 13 | 8.2, 8.3, 8.4, 8.5 (depending on TYPO3) |
| 9      | development | calien/typo3-deprecation-causer | 9.0.x-dev  | v13 + v14 | 11, 12, 13 | 8.2, 8.3, 8.4, 8.5                      |

The PHPUnit major of the project selects the matching major of `calien/phpunit-deprecation-causer`.

## Installation

```bash
# TYPO3 13 or 14, testing framework 9
composer require --dev 'calien/typo3-deprecation-causer':'9.0.*@dev'

# TYPO3 14 or 15, testing framework 10
composer require --dev 'calien/typo3-deprecation-causer':'10.0.*@dev'
```

> [!IMPORTANT]
> No version is released yet, neither of this package nor of `calien/phpunit-deprecation-causer`. Until then the
> project needs `"minimum-stability": "dev"` with `"prefer-stable": true`, or requires
> `calien/phpunit-deprecation-causer` with `@dev` as well.

## Configuration

Register the extension in the PHPUnit configuration of the functional tests:

```xml
<phpunit failOnDeprecation="true">
  <source ignoreIndirectDeprecations="true">
    <include>
      <directory>packages/</directory>
    </include>
  </source>
  <extensions>
    <bootstrap class="Calien\Typo3DeprecationCauser\Extension"/>
  </extensions>
</phpunit>
```

The extension does nothing when `ignoreIndirectDeprecations` is off: PHPUnit reports every deprecation then.

Other code that instantiates on behalf of its caller, such as a factory of a third-party extension, can be added
with the comma-separated parameter `passThroughPaths`:

```xml
<bootstrap class="Calien\Typo3DeprecationCauser\Extension">
  <parameter name="passThroughPaths" value="/vendor/acme/factory/src/Factory.php"/>
</bootstrap>
```

## What is covered

| Project code does                                                          | Reported               |
|----------------------------------------------------------------------------|------------------------|
| `GeneralUtility::makeInstance()` of a deprecated class                     | yes                    |
| `GeneralUtility::makeInstance()` of a service with a deprecated dependency | yes                    |
| `$this->get()` of such a service in a functional test                      | yes                    |
| `new` of a deprecated class                                                | yes, by PHPUnit itself |
| Third-party code instantiates a deprecated class                           | no                     |

## Limitations

- **Deprecations about configuration** are not attributed: core raises them while it processes TCA, FlexForms,
  plugin registrations or TSconfig of the project, and while it runs `ext_localconf.php` code from its cache file.
  No frame of the project is on the stack then.
- **Resolution started by the core** is not attributed: when the core instantiates a project's event listener or
  middleware that needs a deprecated service, only core and container frames are on the stack.
- The limitations of `calien/phpunit-deprecation-causer` apply as well, such as tests in separate processes.

## Development

See [DEVELOPERS.md](DEVELOPERS.md).
