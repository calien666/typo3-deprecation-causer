<?php

declare(strict_types=1);

namespace Calien\Typo3DeprecationCauser\Tests\EndToEnd\Fixtures\Extensions\ThirdParty;

use TYPO3\CMS\Core\Utility\GeneralUtility;

final class ThirdPartyCode
{
    public function instantiateDeprecatedThroughMakeInstance(): void
    {
        GeneralUtility::makeInstance(DeprecatedService::class);
    }
}
