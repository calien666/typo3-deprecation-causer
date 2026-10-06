<?php

declare(strict_types=1);

namespace Calien\Typo3DeprecationCauser;

use Calien\PhpUnitDeprecationCauser\DeprecationCauserRegistrar;
use Calien\PhpUnitDeprecationCauser\PassThroughPaths;
use PHPUnit\Runner\Extension\Extension as PhpUnitExtension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

/**
 * Reports deprecations that project code causes through `GeneralUtility::makeInstance()`, dependency injection or
 * `$this->get()` of a functional test, although `<source ignoreIndirectDeprecations="true">` is set.
 *
 * Register it in the `<extensions>` section of the PHPUnit configuration. The optional, comma-separated parameter
 * `passThroughPaths` adds paths of other code that instantiates on behalf of its caller.
 */
final class Extension implements PhpUnitExtension
{
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        (new DeprecationCauserRegistrar())->register(
            $configuration,
            $facade,
            Typo3PassThroughPaths::create()->merge(PassThroughPaths::fromParameters($parameters)),
        );
    }
}
