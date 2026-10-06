<?php

declare(strict_types=1);

namespace Calien\Typo3DeprecationCauser;

use Calien\PhpUnitDeprecationCauser\FirstPartyCode;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * The `Configuration/TCA/` files below the `<source>` directories of PHPUnit, to tell whether a table or field the
 * core migrated belongs to the project.
 */
final class FirstPartyTcaFiles
{
    /**
     * @var list<non-empty-string>|null
     */
    private ?array $files = null;

    public function __construct(private readonly FirstPartyCode $firstPartyCode) {}

    /**
     * A field belongs to the project when a first-party file defining its table, or overriding it, names the field.
     * A migration of the table itself belongs to the project only when the project defines the table.
     *
     * @return non-empty-string|null
     */
    public function owner(string $table, ?string $field): ?string
    {
        foreach ($this->files() as $file) {
            if ($this->owns($file, $table, $field)) {
                return $file;
            }
        }
        return null;
    }

    private function owns(string $file, string $table, ?string $field): bool
    {
        $definesTable = basename($file) === $table . '.php' && basename(dirname($file)) === 'TCA';
        if ($field === null) {
            return $definesTable;
        }
        $content = (string)file_get_contents($file);
        return ($definesTable || $this->names($content, $table)) && $this->names($content, $field);
    }

    private function names(string $content, string $identifier): bool
    {
        return str_contains($content, '\'' . $identifier . '\'') || str_contains($content, '"' . $identifier . '"');
    }

    /**
     * @return list<non-empty-string>
     */
    private function files(): array
    {
        if ($this->files !== null) {
            return $this->files;
        }
        $this->files = [];
        foreach ($this->firstPartyCode->directories() as $directory) {
            if (!is_dir($directory)) {
                continue;
            }
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
            );
            /** @var SplFileInfo $fileInfo */
            foreach ($iterator as $fileInfo) {
                $file = $fileInfo->getRealPath();
                if ($file !== false && $file !== '' && $this->isTcaFile($file)) {
                    $this->files[] = $file;
                }
            }
        }
        return $this->files;
    }

    private function isTcaFile(string $file): bool
    {
        return preg_match('#/Configuration/TCA/(Overrides/)?[^/]+\.php$#', $file) === 1
            && $this->firstPartyCode->includes($file);
    }
}
