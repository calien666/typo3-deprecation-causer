<?php

declare(strict_types=1);

namespace Calien\Typo3DeprecationCauser;

use Calien\PhpUnitDeprecationCauser\Cause;
use Calien\PhpUnitDeprecationCauser\CauseResolver;
use Calien\PhpUnitDeprecationCauser\FirstPartyCode;
use TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Attributes the deprecation of the core's on-the-fly migration of a FlexForm field to the first-party TCA file that
 * configures the data structure: by a `FILE:` reference to a first-party file, or inline. The message names only
 * the migrated field, so the data structure is taken from the identifier the core parses, an argument on the stack.
 *
 * @phpstan-import-type StackFrame from \Calien\PhpUnitDeprecationCauser\CausingFileLocator
 */
final readonly class FlexFormMigrationCauseResolver implements CauseResolver
{
    private const CONTEXT = 'FlexFormTools did an on-the-fly migration of a flex form data structure.';

    public function __construct(
        private FirstPartyCode $firstPartyCode,
        private FirstPartyTcaFiles $firstPartyTcaFiles,
    ) {}

    public function cause(array $trace, string $message): ?Cause
    {
        if (!str_starts_with($message, self::CONTEXT)) {
            return null;
        }
        $frame = $this->parsingFrame($trace);
        $identifier = json_decode($this->string($frame['args'][0] ?? null), true);
        if (!is_array($identifier) || ($identifier['type'] ?? null) !== 'tca') {
            return null;
        }
        $table = $this->string($identifier['tableName'] ?? null);
        $dataStructure = $this->dataStructure(
            $frame['args'][1] ?? null,
            $table,
            $this->string($identifier['fieldName'] ?? null),
            $this->string($identifier['dataStructureKey'] ?? 'default'),
        );
        if ($dataStructure === '') {
            return null;
        }
        $owner = str_starts_with($dataStructure, 'FILE:')
            ? $this->ownerOfFile($table, $dataStructure)
            : $this->firstPartyTcaFiles->ownerOfText($table, '<' . $this->migratedElement($message) . '>');
        return $owner === null ? Cause::thirdParty() : Cause::inFile($owner);
    }

    /**
     * PHPUnit classifies PHP files only, so a first-party data structure file is attributed to the TCA file that
     * references it.
     *
     * @return non-empty-string|null
     */
    private function ownerOfFile(string $table, string $dataStructure): ?string
    {
        $file = realpath(GeneralUtility::getFileAbsFileName(trim(substr($dataStructure, 5))));
        if ($file === false || !$this->isFirstPartyPath($file)) {
            return null;
        }
        return $this->firstPartyTcaFiles->ownerOfText($table, $dataStructure);
    }

    private function isFirstPartyPath(string $file): bool
    {
        foreach ($this->firstPartyCode->directories() as $directory) {
            $directory = realpath($directory);
            if ($directory !== false && str_starts_with($file, $directory . '/')) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param list<StackFrame> $trace
     * @return StackFrame|null
     */
    private function parsingFrame(array $trace): ?array
    {
        foreach ($trace as $frame) {
            if (($frame['class'] ?? null) === FlexFormTools::class && $frame['function'] === 'parseDataStructureByIdentifier') {
                return $frame;
            }
        }
        return null;
    }

    /**
     * The data structure as the core resolves it for a record type: the type's `columnsOverrides` first, the column
     * itself otherwise. TYPO3 14 passes the TCA of the table while it builds the schema, and a schema later on, when
     * the global TCA is complete. TYPO3 13 keys the data structures of a column by the data structure key.
     */
    private function dataStructure(mixed $schema, string $table, string $field, string $dataStructureKey): string
    {
        $tca = is_array($schema) ? $schema : $this->value($GLOBALS['TCA'] ?? null, $table);
        $dataStructure = $this->value($tca, 'types', $dataStructureKey, 'columnsOverrides', $field, 'config', 'ds')
            ?? $this->value($tca, 'columns', $field, 'config', 'ds');
        if (is_array($dataStructure)) {
            $dataStructure = $dataStructure[$dataStructureKey] ?? null;
        }
        return is_string($dataStructure) ? trim($dataStructure) : '';
    }

    private function string(mixed $value): string
    {
        return is_string($value) ? $value : '';
    }

    private function value(mixed $array, string ...$keys): mixed
    {
        foreach ($keys as $key) {
            if (!is_array($array) || !array_key_exists($key, $array)) {
                return null;
            }
            $array = $array[$key];
        }
        return $array;
    }

    private function migratedElement(string $message): string
    {
        return preg_match('/flex form definition "([^"]+)"/', $message, $matches) === 1 ? $matches[1] : '';
    }
}
