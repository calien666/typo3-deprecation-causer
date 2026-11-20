<?php

declare(strict_types=1);

namespace Calien\Typo3DeprecationCauser\Tests\EndToEnd\Fixtures\Scenarios;

use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Backend\Configuration\SiteTcaConfiguration;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * The test reads the site TCA itself, in which only a third-party field needs a migration. Run by {@see \Calien\Typo3DeprecationCauser\Tests\EndToEnd\ExtensionTest} in a separate PHPUnit process.
 */
final class ThirdPartySiteTcaReadByTestScenarios extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        __DIR__ . '/../Extensions/third_party_site',
    ];

    #[Test]
    public function readSiteTca(): void
    {
        $subject = $this->get(SiteTcaConfiguration::class);
        self::assertInstanceOf(SiteTcaConfiguration::class, $subject);

        self::assertNotSame([], $subject->getTca());
    }
}
