<?php

declare(strict_types=1);

namespace Calien\Typo3DeprecationCauser\Tests\EndToEnd\Fixtures\Extensions\ThirdParty;

/**
 * Stands in for a core API that extensions call from their bootstrap files.
 */
final class DeprecatedApi
{
    public static function register(string $caller): void
    {
        trigger_error(sprintf('DeprecatedApi::register() is deprecated, called from %s.', $caller), E_USER_DEPRECATED);
    }
}
