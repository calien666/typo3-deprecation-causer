<?php

declare(strict_types=1);

namespace Calien\Typo3DeprecationCauser;

use Calien\PhpUnitDeprecationCauser\PassThroughPaths;

/**
 * The files of TYPO3 and its testing framework that instantiate classes on behalf of their caller.
 */
final class Typo3PassThroughPaths
{
    public static function create(): PassThroughPaths
    {
        return new PassThroughPaths([
            // Composer installations load the core from `typo3/cms-core`, the core repository from `sysext/core`.
            '/cms-core/Classes/Utility/GeneralUtility.php',
            '/cms-core/Classes/DependencyInjection/',
            '/sysext/core/Classes/Utility/GeneralUtility.php',
            '/sysext/core/Classes/DependencyInjection/',
            '/symfony/dependency-injection/',
            '/var/cache/code/di/',
            // `$this->get()` of functional tests, so a test fetching a service counts as its caller.
            '/testing-framework/Classes/Core/Functional/FunctionalTestCase.php',
        ]);
    }
}
