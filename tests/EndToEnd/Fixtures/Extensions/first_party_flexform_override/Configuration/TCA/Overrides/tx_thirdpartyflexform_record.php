<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die();

(static function (): void {
    $dataStructures = [
        'project_file' => 'FILE:EXT:first_party_flexform_override/Configuration/FlexForms/Override.xml',
        // The field is migrated to type "datetime" with a deprecation on every core.
        'project_inline' => '
            <T3DataStructure>
                <sheets>
                    <sDEF>
                        <ROOT>
                            <sheetTitle>General</sheetTitle>
                            <type>array</type>
                            <el>
                                <project_override_inline_date>
                                    <label>Project override inline date</label>
                                    <config>
                                        <type>input</type>
                                        <renderType>inputDateTime</renderType>
                                    </config>
                                </project_override_inline_date>
                            </el>
                        </ROOT>
                    </sDEF>
                </sheets>
            </T3DataStructure>
        ',
    ];
    $columnsOverrides = (new Typo3Version())->getMajorVersion() >= 14;
    foreach ($dataStructures as $recordType => $dataStructure) {
        ExtensionManagementUtility::addRecordType(
            [
                'label' => $recordType,
                'value' => $recordType,
            ],
            'record_type, title, settings',
            $columnsOverrides
                ? [
                    'columnsOverrides' => [
                        'settings' => [
                            'config' => [
                                'ds' => $dataStructure,
                            ],
                        ],
                    ],
                ]
                : [],
            '',
            'tx_thirdpartyflexform_record',
        );
    }
    // TYPO3 13 selects the data structure by the pointer field "record_type".
    if (!$columnsOverrides) {
        $GLOBALS['TCA'] = array_replace_recursive(
            (array)$GLOBALS['TCA'],
            [
                'tx_thirdpartyflexform_record' => [
                    'columns' => [
                        'settings' => [
                            'config' => [
                                'ds' => $dataStructures,
                            ],
                        ],
                    ],
                ],
            ],
        );
    }
})();
