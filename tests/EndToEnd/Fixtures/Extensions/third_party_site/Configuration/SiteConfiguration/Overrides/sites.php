<?php

declare(strict_types=1);

defined('TYPO3') or die();

(static function (): void {
    $GLOBALS['SiteConfiguration'] = array_replace_recursive(
        (array)$GLOBALS['SiteConfiguration'],
        [
            'site' => [
                'columns' => [
                    'vendor_date' => [
                        'label' => 'Vendor date',
                        'config' => [
                            // Migrated to type "datetime" with a deprecation on every core.
                            'type' => 'input',
                            'renderType' => 'inputDateTime',
                        ],
                    ],
                ],
            ],
        ],
    );
})();
