<?php

declare(strict_types=1);

namespace Calien\Typo3DeprecationCauser\Tests\EndToEnd\Fixtures\Scenarios;

use Calien\Typo3DeprecationCauser\Tests\EndToEnd\Fixtures\Extensions\FirstParty\ProjectCode;
use Calien\Typo3DeprecationCauser\Tests\EndToEnd\Fixtures\Extensions\FirstParty\ServiceWithDeprecatedDependency;
use Calien\Typo3DeprecationCauser\Tests\EndToEnd\Fixtures\Extensions\ThirdParty\DeprecatedService;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Run by {@see \Calien\Typo3DeprecationCauser\Tests\EndToEnd\ExtensionTest} in a separate PHPUnit process,
 * one test at a time.
 */
final class DeprecationScenarios extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        __DIR__ . '/../Extensions/third_party',
        __DIR__ . '/../Extensions/first_party',
    ];

    #[Test]
    public function projectInstantiatesDeprecatedThroughMakeInstance(): void
    {
        $this->expectNotToPerformAssertions();
        (new ProjectCode())->instantiateDeprecatedThroughMakeInstance();
    }

    #[Test]
    public function projectInstantiatesServiceWithDeprecatedDependency(): void
    {
        $this->expectNotToPerformAssertions();
        (new ProjectCode())->instantiateServiceWithDeprecatedDependency();
    }

    #[Test]
    public function testGetsServiceWithDeprecatedDependencyFromContainer(): void
    {
        $this->expectNotToPerformAssertions();
        $this->get(ServiceWithDeprecatedDependency::class);
    }

    #[Test]
    public function testInstantiatesDeprecatedThroughMakeInstance(): void
    {
        $this->expectNotToPerformAssertions();
        GeneralUtility::makeInstance(DeprecatedService::class);
    }

    #[Test]
    public function thirdPartyInstantiatesDeprecatedThroughMakeInstance(): void
    {
        $this->expectNotToPerformAssertions();
        (new ProjectCode())->letThirdPartyInstantiateDeprecatedThroughMakeInstance();
    }

    #[Test]
    public function projectInstantiatesWithoutDeprecation(): void
    {
        $this->expectNotToPerformAssertions();
        (new ProjectCode())->instantiateWithoutDeprecation();
    }

    #[Test]
    #[IgnoreDeprecations]
    public function expectedDeprecationThroughMakeInstance(): void
    {
        $this->expectUserDeprecationMessage('DeprecatedService is deprecated.');
        (new ProjectCode())->instantiateDeprecatedThroughMakeInstance();
    }
}
