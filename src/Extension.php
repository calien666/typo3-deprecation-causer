<?php

declare(strict_types=1);

namespace Calien\Typo3DeprecationCauser;

use Calien\PhpUnitDeprecationCauser\DeprecationCauserRegistrar;
use Calien\PhpUnitDeprecationCauser\FirstPartyCode;
use Calien\PhpUnitDeprecationCauser\PassThroughPaths;
use PHPUnit\Runner\Extension\Extension as PhpUnitExtension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

/**
 * Reports deprecations that project code causes through `GeneralUtility::makeInstance()`, dependency injection,
 * `$this->get()` of a functional test or its `ext_localconf.php` and `ext_tables.php`, and TCA migrations of its
 * tables and fields, although `<source ignoreIndirectDeprecations="true">` is set.
 *
 * Register it in the `<extensions>` section of the PHPUnit configuration. The optional, comma-separated parameter
 * `passThroughPaths` adds paths of other code that instantiates on behalf of its caller.
 */
final class Extension implements PhpUnitExtension
{
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        $firstPartyCode = FirstPartyCode::fromConfiguration($configuration);
        (new DeprecationCauserRegistrar())->register(
            $configuration,
            $facade,
            Typo3PassThroughPaths::create()->merge(PassThroughPaths::fromParameters($parameters)),
            [new CachedBootstrapFileMapper()],
            [
                new ExtTablesMessageCauseResolver($firstPartyCode),
                new TcaMigrationCauseResolver(new FirstPartyTcaFiles($firstPartyCode)),
            ],
        );
    }
}
