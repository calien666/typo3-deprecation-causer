<?php

declare(strict_types=1);

namespace Calien\Typo3DeprecationCauser;

use Calien\PhpUnitDeprecationCauser\Cause;
use Calien\PhpUnitDeprecationCauser\CauseResolver;
use Calien\PhpUnitDeprecationCauser\FirstPartyCode;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Attributes the deprecation of loading `ext_tables.php`, raised by TYPO3 14, to the `ext_tables.php` of the
 * extension it names, when that file is first-party code.
 */
final readonly class ExtTablesMessageCauseResolver implements CauseResolver
{
    public function __construct(private FirstPartyCode $firstPartyCode) {}

    public function cause(array $trace, string $message): ?Cause
    {
        if (preg_match('/^Loading ext_tables\.php of extension "([^"]+)"/', $message, $matches) !== 1) {
            return null;
        }
        if (!ExtensionManagementUtility::isLoaded($matches[1])) {
            return Cause::thirdParty();
        }
        $file = ExtensionManagementUtility::extPath($matches[1], 'ext_tables.php');
        return $file !== '' && $this->firstPartyCode->includes($file) ? Cause::inFile($file) : Cause::thirdParty();
    }
}
