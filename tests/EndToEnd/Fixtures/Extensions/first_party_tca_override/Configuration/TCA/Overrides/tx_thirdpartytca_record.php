<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die();

(static function (): void {
    $newColumnsArray = [
        'project_date' => [
            'label' => 'Project date',
            'config' => [
                // Migrated to type "datetime" with a deprecation on every core.
                'type' => 'input',
                'renderType' => 'inputDateTime',
            ],
        ],
    ];
    ExtensionManagementUtility::addTCAcolumns('tx_thirdpartytca_record', $newColumnsArray);
})();
