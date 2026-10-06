<?php

declare(strict_types=1);

namespace Calien\Typo3DeprecationCauser\Tests\EndToEnd\Fixtures\Scenarios;

use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * The project's own table needs a TCA migration, next to a third-party table that needs it, too. Run by {@see \Calien\Typo3DeprecationCauser\Tests\EndToEnd\ExtensionTest} in a separate PHPUnit process.
 */
final class ProjectTcaScenarios extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        __DIR__ . '/../Extensions/third_party_tca',
        __DIR__ . '/../Extensions/first_party_tca',
    ];

    #[Test]
    public function bootWithTcaToMigrate(): void
    {
        $this->expectNotToPerformAssertions();
    }
}
