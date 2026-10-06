<?php

declare(strict_types=1);

namespace Calien\Typo3DeprecationCauser\Tests\EndToEnd\Fixtures\Extensions\FirstParty;

use Calien\Typo3DeprecationCauser\Tests\EndToEnd\Fixtures\Extensions\ThirdParty\DeprecatedService;
use Calien\Typo3DeprecationCauser\Tests\EndToEnd\Fixtures\Extensions\ThirdParty\ThirdPartyCode;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class ProjectCode
{
    public function instantiateDeprecatedThroughMakeInstance(): void
    {
        GeneralUtility::makeInstance(DeprecatedService::class);
    }

    public function instantiateServiceWithDeprecatedDependency(): void
    {
        GeneralUtility::makeInstance(ServiceWithDeprecatedDependency::class);
    }

    public function letThirdPartyInstantiateDeprecatedThroughMakeInstance(): void
    {
        (new ThirdPartyCode())->instantiateDeprecatedThroughMakeInstance();
    }

    public function instantiateWithoutDeprecation(): void
    {
        GeneralUtility::makeInstance(self::class);
    }
}
