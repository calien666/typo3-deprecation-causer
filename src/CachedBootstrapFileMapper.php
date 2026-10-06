<?php

declare(strict_types=1);

namespace Calien\Typo3DeprecationCauser;

use Calien\PhpUnitDeprecationCauser\GeneratedFileMapper;

/**
 * Maps the cache files the core concatenates from the `ext_localconf.php` and `ext_tables.php` files of all
 * extensions back to the file of the extension a line came from. The core heads the code of every extension with a
 * `File:` comment naming that file.
 */
final class CachedBootstrapFileMapper implements GeneratedFileMapper
{
    /**
     * @var array<string, list<array{int, non-empty-string}>>
     */
    private array $sectionsByCacheFile = [];

    public function sourceFile(string $file, int $line): ?string
    {
        if (preg_match('#/cache/code/core/ext_(localconf|tables)_[0-9a-f]+\.php$#', str_replace('\\', '/', $file)) !== 1) {
            return null;
        }
        $sourceFile = null;
        foreach ($this->sections($file) as [$firstLine, $sectionFile]) {
            if ($firstLine > $line) {
                break;
            }
            $sourceFile = $sectionFile;
        }
        return $sourceFile;
    }

    /**
     * @return list<array{int, non-empty-string}> The line of each `File:` header and the file it names, in order
     */
    private function sections(string $cacheFile): array
    {
        if (isset($this->sectionsByCacheFile[$cacheFile])) {
            return $this->sectionsByCacheFile[$cacheFile];
        }
        $sections = [];
        $lines = file($cacheFile, FILE_IGNORE_NEW_LINES);
        foreach ($lines === false ? [] : $lines as $index => $content) {
            if (preg_match('/^ \* File: (.+)$/', $content, $matches) === 1) {
                $sections[] = [$index + 1, $matches[1]];
            }
        }
        return $this->sectionsByCacheFile[$cacheFile] = $sections;
    }
}
