<?php

declare(strict_types=1);

namespace Calien\Typo3DeprecationCauser\Tests\EndToEnd\Fixtures\Scenarios;

use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * The project adds a field needing a TCA migration to a third-party table. Run by {@see \Calien\Typo3DeprecationCauser\Tests\EndToEnd\ExtensionTest} in a separate PHPUnit process.
 */
final class ProjectTcaOverrideScenarios extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        __DIR__ . '/../Extensions/third_party_tca',
        __DIR__ . '/../Extensions/first_party_tca_override',
    ];

    #[Test]
    public function bootWithTcaToMigrate(): void
    {
        $this->expectNotToPerformAssertions();
    }
}
