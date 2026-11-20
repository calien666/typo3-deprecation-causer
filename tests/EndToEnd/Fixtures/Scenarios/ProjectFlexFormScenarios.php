<?php

declare(strict_types=1);

namespace Calien\Typo3DeprecationCauser\Tests\EndToEnd\Fixtures\Scenarios;

use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * A project table has a FlexForm from a file and an inline one, both needing a migration. Run by {@see \Calien\Typo3DeprecationCauser\Tests\EndToEnd\ExtensionTest} in a separate PHPUnit process.
 */
final class ProjectFlexFormScenarios extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        __DIR__ . '/../Extensions/first_party_flexform',
    ];

    #[Test]
    public function bootWithFlexFormToMigrate(): void
    {
        $this->expectNotToPerformAssertions();
    }
}
