<?php

declare(strict_types=1);

namespace Calien\Typo3DeprecationCauser\Tests\EndToEnd\Fixtures\Scenarios;

use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * The project's `ext_localconf.php` and `ext_tables.php` call a deprecated API while the instance boots from them.
 * Run by {@see \Calien\Typo3DeprecationCauser\Tests\EndToEnd\ExtensionTest} in a separate PHPUnit process.
 */
final class BootstrapScenarios extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        __DIR__ . '/../Extensions/third_party',
        __DIR__ . '/../Extensions/first_party_bootstrap',
    ];

    #[Test]
    public function bootFromBootstrapFiles(): void
    {
        $this->expectNotToPerformAssertions();
    }
}
