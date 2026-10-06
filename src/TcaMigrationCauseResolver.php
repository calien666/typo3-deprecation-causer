<?php

declare(strict_types=1);

namespace Calien\Typo3DeprecationCauser;

use Calien\PhpUnitDeprecationCauser\MessageCauseResolver;

/**
 * Attributes the deprecation of the core's TCA migration to the project when it migrated a table or field the
 * project defines. The deprecation lists the migrations of all extensions; it stays suppressed when none of them
 * belongs to the project.
 */
final readonly class TcaMigrationCauseResolver implements MessageCauseResolver
{
    public function __construct(private FirstPartyTcaFiles $firstPartyTcaFiles) {}

    public function causingFile(string $message): ?string
    {
        foreach (TcaMigrationMessage::migratedItems($message) as [$table, $field]) {
            $owner = $this->firstPartyTcaFiles->owner($table, $field);
            if ($owner !== null) {
                return $owner;
            }
        }
        return null;
    }
}
