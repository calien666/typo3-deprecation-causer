<?php

declare(strict_types=1);

namespace Calien\Typo3DeprecationCauser;

/**
 * Reads the tables and fields from the deprecations the core raises after migrating the TCA during bootstrap, or the
 * site TCA whenever it is read: a context line followed by one line per migration.
 */
final class TcaMigrationMessage
{
    private const CONTEXT = 'Automatic TCA migration done during bootstrap.';

    private const SITE_CONTEXT = 'Automatic TCA migration done during bootstrap of Site TCA Configuration.';

    /**
     * Patterns of the migration messages of TYPO3 13 to 15, naming a field and its table first, a table only last.
     */
    private const PATTERNS = [
        "/field '(?<field>[^']+)' (?:of|in) table '(?<table>[^']+)'/",
        "/column override '(?<field>[^']+)' of table '(?<table>[^']+)'/",
        "/(?<table>\\w+)\\['columns'\\]\\['(?<field>[^']+)'\\]/",
        "/field '(?<field>[^']+)' of '(?<table>[^']+)'/",
        "/table '(?<table>[^']+)'/",
        "/The '(?<table>[^']+)' TCA/",
    ];

    /**
     * @return list<array{string, string|null}> Table and field, or null for a migration of the table itself
     */
    public static function migratedItems(string $message): array
    {
        return str_starts_with($message, self::CONTEXT) ? self::items($message) : [];
    }

    /**
     * @return list<array{string, string|null}> Site table and field, or null for a migration of the table itself
     */
    public static function migratedSiteItems(string $message): array
    {
        return str_starts_with($message, self::SITE_CONTEXT) ? self::items($message) : [];
    }

    /**
     * @return list<array{string, string|null}>
     */
    private static function items(string $message): array
    {
        $items = [];
        foreach (array_slice(explode("\n", $message), 1) as $line) {
            $item = self::migratedItem($line);
            if ($item !== null) {
                $items[] = $item;
            }
        }
        return $items;
    }

    /**
     * @return array{string, string|null}|null
     */
    private static function migratedItem(string $line): ?array
    {
        foreach (self::PATTERNS as $pattern) {
            if (preg_match($pattern, $line, $matches) === 1) {
                return [$matches['table'], ($matches['field'] ?? '') === '' ? null : $matches['field']];
            }
        }
        return null;
    }
}
