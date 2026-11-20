<?php

declare(strict_types=1);

namespace Calien\Typo3DeprecationCauser\Tests\EndToEnd\Fixtures\Scenarios;

use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * The project adds record types with FlexForms needing a migration to a third-party table, whose own FlexForm needs one as well. Run by {@see \Calien\Typo3DeprecationCauser\Tests\EndToEnd\ExtensionTest} in a separate PHPUnit process.
 */
final class ProjectFlexFormOverrideScenarios extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        __DIR__ . '/../Extensions/third_party_flexform',
        __DIR__ . '/../Extensions/first_party_flexform_override',
    ];

    #[Test]
    public function bootWithFlexFormToMigrate(): void
    {
        $this->expectNotToPerformAssertions();
    }
}
