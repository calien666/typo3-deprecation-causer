<?php

declare(strict_types=1);

defined('TYPO3') or die();

(static function (): void {
    $GLOBALS['SiteConfiguration'] = array_replace_recursive(
        (array)$GLOBALS['SiteConfiguration'],
        [
            'site' => [
                'columns' => [
                    'vendor_scheme' => [
                        'label' => 'Vendor scheme',
                        'config' => [
                            'type' => 'input',
                            'valuePicker' => [
                                // Legacy item list, migrated with a deprecation on every core.
                                'items' => [
                                    ['HTTPS', 'https'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    );
})();
