<?php

declare(strict_types=1);

namespace Calien\Typo3DeprecationCauser\Tests\EndToEnd\Fixtures\Extensions\ThirdParty;

final class DeprecatedService
{
    public function __construct()
    {
        trigger_error('DeprecatedService is deprecated.', E_USER_DEPRECATED);
    }
}
