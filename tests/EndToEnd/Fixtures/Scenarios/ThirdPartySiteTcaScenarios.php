<?php

declare(strict_types=1);

namespace Calien\Typo3DeprecationCauser\Tests\EndToEnd\Fixtures\Scenarios;

use Calien\Typo3DeprecationCauser\Tests\EndToEnd\Fixtures\Extensions\ThirdPartySite\SiteTcaReader;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Core code reads the site TCA, in which only a third-party field needs a migration. Run by {@see \Calien\Typo3DeprecationCauser\Tests\EndToEnd\ExtensionTest} in a separate PHPUnit process.
 */
final class ThirdPartySiteTcaScenarios extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        __DIR__ . '/../Extensions/third_party_site',
    ];

    #[Test]
    public function readSiteTca(): void
    {
        $subject = $this->get(SiteTcaReader::class);
        self::assertInstanceOf(SiteTcaReader::class, $subject);

        self::assertNotSame([], $subject->read());
    }
}
