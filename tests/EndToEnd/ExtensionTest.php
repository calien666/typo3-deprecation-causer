<?php

declare(strict_types=1);

namespace Calien\Typo3DeprecationCauser\Tests\EndToEnd;

use Calien\Typo3DeprecationCauser\Extension;
use Calien\Typo3DeprecationCauser\Tests\EndToEnd\Fixtures\Scenarios\DeprecationScenarios;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Configuration\Extension\ExtTablesFactory;
use TYPO3\CMS\Core\Information\Typo3Version;

/**
 * Runs each scenario of {@see DeprecationScenarios} in its own PHPUnit process with one of the configurations in
 * `Fixtures/`, and checks the deprecations that run reports. Each process boots a TYPO3 functional test instance.
 */
#[CoversClass(Extension::class)]
final class ExtensionTest extends TestCase
{
    #[Test]
    #[DataProvider('scenarioProvider')]
    public function reportsDeprecationsCausedByProjectCode(
        string $configuration,
        string $scenario,
        int $expectedDeprecations,
    ): void {
        [$exitCode, $output] = $this->runScenario($configuration, sprintf('/::%s$/', $scenario));

        self::assertMatchesRegularExpression('/^OK \(1 test|^Tests: 1,/m', $output, $output);
        self::assertSame($expectedDeprecations, $this->reportedDeprecations($output), $output);
        self::assertSame($expectedDeprecations > 0 ? 1 : 0, $exitCode, $output);
    }

    public static function scenarioProvider(): Generator
    {
        $expectations = [
            'projectInstantiatesDeprecatedThroughMakeInstance' => [1, 0, 1],
            'projectInstantiatesServiceWithDeprecatedDependency' => [1, 0, 1],
            'testGetsServiceWithDeprecatedDependencyFromContainer' => [1, 0, 1],
            'testInstantiatesDeprecatedThroughMakeInstance' => [1, 0, 1],
            'thirdPartyInstantiatesDeprecatedThroughMakeInstance' => [0, 0, 1],
            'projectInstantiatesWithoutDeprecation' => [0, 0, 0],
        ];
        $configurations = ['ignoring-indirect', 'ignoring-indirect-without-extension', 'reporting-indirect'];
        foreach ($expectations as $scenario => $expectedDeprecations) {
            foreach ($configurations as $index => $configuration) {
                yield sprintf('%s with %s', $scenario, $configuration) => [
                    'configuration' => $configuration,
                    'scenario' => $scenario,
                    'expectedDeprecations' => $expectedDeprecations[$index],
                ];
            }
        }
    }

    #[Test]
    #[DataProvider('bootstrapScenarioProvider')]
    #[DataProvider('tcaScenarioProvider')]
    public function reportsDeprecationsCausedByProjectConfiguration(
        string $configuration,
        string $scenarios,
        int $tests,
        int $expectedDeprecations,
    ): void {
        [$exitCode, $output] = $this->runScenario($configuration, sprintf('/\\\\%s::/', $scenarios));

        self::assertMatchesRegularExpression(sprintf('/^OK \\(%1$d tests?|^Tests: %1$d,/m', $tests), $output, $output);
        self::assertSame($expectedDeprecations, $this->reportedDeprecations($output), $output);
        self::assertSame($expectedDeprecations > 0 ? 1 : 0, $exitCode, $output);
    }

    /**
     * TYPO3 15 no longer loads `ext_tables.php`; TYPO3 14 loads it and deprecates loading it, once while loading the
     * single files and once while concatenating them into the cache file; TYPO3 13 loads it without deprecation.
     */
    public static function bootstrapScenarioProvider(): Generator
    {
        $extTables = class_exists(ExtTablesFactory::class) ? 1 : 0;
        $extTablesDeprecated = $extTables === 1 && (new Typo3Version())->getMajorVersion() >= 14 ? 1 : 0;
        yield 'bootstrap files with ignoring-indirect' => [
            'configuration' => 'ignoring-indirect',
            'scenarios' => 'BootstrapScenarios',
            'tests' => 1,
            'expectedDeprecations' => 1 + $extTables + 2 * $extTablesDeprecated,
        ];
        yield 'bootstrap files with ignoring-indirect-without-extension' => [
            'configuration' => 'ignoring-indirect-without-extension',
            'scenarios' => 'BootstrapScenarios',
            'tests' => 1,
            'expectedDeprecations' => 1 + $extTables,
        ];
        yield 'bootstrap files with reporting-indirect' => [
            'configuration' => 'reporting-indirect',
            'scenarios' => 'BootstrapScenarios',
            'tests' => 1,
            'expectedDeprecations' => 1 + $extTables + 2 * $extTablesDeprecated,
        ];
        yield 'cached bootstrap files with ignoring-indirect' => [
            'configuration' => 'ignoring-indirect',
            'scenarios' => 'CachedBootstrapScenarios',
            'tests' => 2,
            'expectedDeprecations' => 1 + $extTables,
        ];
        yield 'cached bootstrap files with ignoring-indirect-without-extension' => [
            'configuration' => 'ignoring-indirect-without-extension',
            'scenarios' => 'CachedBootstrapScenarios',
            'tests' => 2,
            'expectedDeprecations' => 0,
        ];
        yield 'cached bootstrap files with reporting-indirect' => [
            'configuration' => 'reporting-indirect',
            'scenarios' => 'CachedBootstrapScenarios',
            'tests' => 2,
            'expectedDeprecations' => 1 + $extTables,
        ];
    }

    /**
     * The core reports all TCA migrations of an instance in one deprecation.
     */
    public static function tcaScenarioProvider(): Generator
    {
        $expectations = [
            'ProjectTcaScenarios' => [1, 0, 1],
            'ProjectTcaOverrideScenarios' => [1, 0, 1],
            'ThirdPartyTcaScenarios' => [0, 0, 1],
        ];
        $configurations = ['ignoring-indirect', 'ignoring-indirect-without-extension', 'reporting-indirect'];
        foreach ($expectations as $scenarios => $expectedDeprecations) {
            foreach ($configurations as $index => $configuration) {
                yield sprintf('%s with %s', $scenarios, $configuration) => [
                    'configuration' => $configuration,
                    'scenarios' => $scenarios,
                    'tests' => 1,
                    'expectedDeprecations' => $expectedDeprecations[$index],
                ];
            }
        }
    }

    #[Test]
    public function expectedDeprecationCausedByProjectCodeDoesNotFailTheRun(): void
    {
        [$exitCode, $output] = $this->runScenario('ignoring-indirect', '/::expectedDeprecationThroughMakeInstance$/');

        self::assertSame(0, $exitCode, $output);
        self::assertSame(0, $this->reportedDeprecations($output), $output);
    }

    /**
     * @return array{int, string}
     */
    private function runScenario(string $configuration, string $filter): array
    {
        $command = [
            PHP_BINARY,
            dirname(__DIR__, 2) . '/.Build/vendor/phpunit/phpunit/phpunit',
            '--configuration',
            __DIR__ . '/Fixtures/' . $configuration . '.xml',
            '--filter',
            $filter,
        ];
        $process = proc_open($command, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        self::assertIsResource($process);
        $output = (string)stream_get_contents($pipes[1]);
        $output .= (string)stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        return [proc_close($process), $output];
    }

    private function reportedDeprecations(string $output): int
    {
        return preg_match('/(?<!PHPUnit )Deprecations: (\d+)/', $output, $matches) === 1 ? (int)$matches[1] : 0;
    }
}
