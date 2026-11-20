<?php

declare(strict_types=1);

namespace Calien\Typo3DeprecationCauser\Tests\EndToEnd\Fixtures\Extensions\ThirdPartySite;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use TYPO3\CMS\Backend\Configuration\SiteTcaConfiguration;

/**
 * Reads the site TCA on behalf of its caller, as the core's site configuration module does.
 */
#[Autoconfigure(public: true)]
final readonly class SiteTcaReader
{
    public function __construct(private SiteTcaConfiguration $siteTcaConfiguration) {}

    /**
     * @return array<mixed>
     */
    public function read(): array
    {
        return $this->siteTcaConfiguration->getTca();
    }
}
