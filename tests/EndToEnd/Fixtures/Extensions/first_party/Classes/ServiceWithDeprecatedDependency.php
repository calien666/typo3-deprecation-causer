<?php

declare(strict_types=1);

namespace Calien\Typo3DeprecationCauser\Tests\EndToEnd\Fixtures\Extensions\FirstParty;

use Calien\Typo3DeprecationCauser\Tests\EndToEnd\Fixtures\Extensions\ThirdParty\DeprecatedService;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(public: true)]
final readonly class ServiceWithDeprecatedDependency
{
    public function __construct(public DeprecatedService $deprecatedService) {}
}
