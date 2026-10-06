<?php

declare(strict_types=1);

namespace Calien\Typo3DeprecationCauser\Tests\Unit;

use Calien\Typo3DeprecationCauser\Typo3PassThroughPaths;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

#[CoversClass(Typo3PassThroughPaths::class)]
final class Typo3PassThroughPathsTest extends TestCase
{
    #[Test]
    #[DataProvider('filesProvider')]
    public function matchesFilesThatInstantiateOnBehalfOfTheirCaller(string $file, bool $expected): void
    {
        $subject = Typo3PassThroughPaths::create();

        self::assertSame($expected, $subject->matches($file));
    }

    public static function filesProvider(): Generator
    {
        yield 'makeInstance in a Composer installation' => [
            'file' => '/var/www/html/vendor/typo3/cms-core/Classes/Utility/GeneralUtility.php',
            'expected' => true,
        ];
        yield 'makeInstance in the core repository' => [
            'file' => '/srv/typo3/typo3/sysext/core/Classes/Utility/GeneralUtility.php',
            'expected' => true,
        ];
        yield 'container factory of the core' => [
            'file' => '/var/www/html/vendor/typo3/cms-core/Classes/DependencyInjection/ContainerBuilder.php',
            'expected' => true,
        ];
        yield 'Symfony container' => [
            'file' => '/var/www/html/vendor/symfony/dependency-injection/Container.php',
            'expected' => true,
        ];
        yield 'compiled container of a Composer installation' => [
            'file' => '/var/www/html/var/cache/code/di/DependencyInjectionContainer_1a2b3c.php',
            'expected' => true,
        ];
        yield 'compiled container of a functional test instance' => [
            'file' => '/app/.Build/Web/typo3temp/var/tests/functional-1a2b3c/typo3temp/var/cache/code/di/DependencyInjectionContainer_1a2b3c.php',
            'expected' => true,
        ];
        yield 'functional test case of the testing framework, outside of get()' => [
            'file' => '/var/www/html/vendor/typo3/testing-framework/Classes/Core/Functional/FunctionalTestCase.php',
            'expected' => false,
        ];
        yield 'other core class' => [
            'file' => '/var/www/html/vendor/typo3/cms-core/Classes/DataHandling/DataHandler.php',
            'expected' => false,
        ];
        yield 'other testing framework class' => [
            'file' => '/var/www/html/vendor/typo3/testing-framework/Classes/Core/Testbase.php',
            'expected' => false,
        ];
        yield 'project code' => [
            'file' => '/var/www/html/packages/site/Classes/Service/ProjectService.php',
            'expected' => false,
        ];
    }

    #[Test]
    public function matchesContainerAccessOfFunctionalTests(): void
    {
        $subject = Typo3PassThroughPaths::create();

        self::assertTrue($subject->matchesMethod(FunctionalTestCase::class, 'get'));
        self::assertFalse($subject->matchesMethod(FunctionalTestCase::class, 'setUp'));
    }
}
