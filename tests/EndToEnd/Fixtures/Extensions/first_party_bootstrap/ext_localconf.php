<?php

declare(strict_types=1);

use Calien\Typo3DeprecationCauser\Tests\EndToEnd\Fixtures\Extensions\ThirdParty\DeprecatedApi;

defined('TYPO3') or die();

(static function (): void {
    DeprecatedApi::register('ext_localconf.php');
})();
