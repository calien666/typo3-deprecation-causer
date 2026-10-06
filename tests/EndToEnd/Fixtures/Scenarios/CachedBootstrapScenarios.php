<?php

declare(strict_types=1);

namespace Calien\Typo3DeprecationCauser\Tests\EndToEnd\Fixtures\Scenarios;

use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * The first test boots from the project's `ext_localconf.php` and `ext_tables.php` and ignores what that triggers;
 * the second boots from the cache files the core concatenated from them. Run as a whole by
 * {@see \Calien\Typo3DeprecationCauser\Tests\EndToEnd\ExtensionTest} in a separate PHPUnit process.
 */
final class CachedBootstrapScenarios extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        __DIR__ . '/../Extensions/third_party',
        __DIR__ . '/../Extensions/first_party_bootstrap',
    ];

    #[Test]
    #[IgnoreDeprecations]
    public function bootFromBootstrapFiles(): void
    {
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    public function bootFromCachedBootstrapFiles(): void
    {
        $this->expectNotToPerformAssertions();
    }
}
