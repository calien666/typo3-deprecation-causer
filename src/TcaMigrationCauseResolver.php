<?php

declare(strict_types=1);

namespace Calien\Typo3DeprecationCauser;

use Calien\PhpUnitDeprecationCauser\Cause;
use Calien\PhpUnitDeprecationCauser\CauseResolver;

/**
 * Attributes the deprecation of the core's TCA migration, and of its site TCA migration, to the project when it
 * migrated a table or field the project defines. The deprecation lists the migrations of all extensions; it stays
 * suppressed when none of them belongs to the project, also when a test started the migration of the site TCA.
 */
final readonly class TcaMigrationCauseResolver implements CauseResolver
{
    public function __construct(
        private FirstPartyTcaFiles $firstPartyTcaFiles,
        private FirstPartyTcaFiles $firstPartySiteTcaFiles,
    ) {}

    public function cause(array $trace, string $message): ?Cause
    {
        $files = $this->firstPartyTcaFiles;
        $items = TcaMigrationMessage::migratedItems($message);
        if ($items === []) {
            $files = $this->firstPartySiteTcaFiles;
            $items = TcaMigrationMessage::migratedSiteItems($message);
        }
        if ($items === []) {
            return null;
        }
        foreach ($items as [$table, $field]) {
            $owner = $files->owner($table, $field);
            if ($owner !== null) {
                return Cause::inFile($owner);
            }
        }
        return Cause::thirdParty();
    }
}
