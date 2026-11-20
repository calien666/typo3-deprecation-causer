<?php

declare(strict_types=1);

namespace Calien\Typo3DeprecationCauser\Tests\EndToEnd\Fixtures\Scenarios;

use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Only the FlexForm of a third-party table needs a migration. Run by {@see \Calien\Typo3DeprecationCauser\Tests\EndToEnd\ExtensionTest} in a separate PHPUnit process.
 */
final class ThirdPartyFlexFormScenarios extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        __DIR__ . '/../Extensions/third_party_flexform',
    ];

    #[Test]
    public function bootWithFlexFormToMigrate(): void
    {
        $this->expectNotToPerformAssertions();
    }
}
