<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die();

(static function (): void {
    ExtensionManagementUtility::addRecordType(
        [
            'label' => 'Project file',
            'value' => 'project_file',
        ],
        'record_type, title, settings',
        [
            'columnsOverrides' => [
                'settings' => [
                    'config' => [
                        'ds' => 'FILE:EXT:first_party_flexform_override/Configuration/FlexForms/Override.xml',
                    ],
                ],
            ],
        ],
        '',
        'tx_thirdpartyflexform_record',
    );
    ExtensionManagementUtility::addRecordType(
        [
            'label' => 'Project inline',
            'value' => 'project_inline',
        ],
        'record_type, title, settings',
        [
            'columnsOverrides' => [
                'settings' => [
                    'config' => [
                        // The field is migrated to type "datetime" with a deprecation on every core.
                        'ds' => '
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
                    ],
                ],
            ],
        ],
        '',
        'tx_thirdpartyflexform_record',
    );
})();
