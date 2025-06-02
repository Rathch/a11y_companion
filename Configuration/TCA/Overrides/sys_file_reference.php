<?php

use Cru\A11yCompanion\Evaluation\AlternativeTextNotEmpty;

if (isset($GLOBALS['TCA']['sys_file_reference']['columns']['alternative']['config']['eval']) && $GLOBALS['TCA']['sys_file_reference']['columns']['alternative']['config']['eval'] !== '') {
    $GLOBALS['TCA']['sys_file_reference']['columns']['alternative']['config']['eval'] .= ',' . AlternativeTextNotEmpty::class;
} else {
    $GLOBALS['TCA']['sys_file_reference']['columns']['alternative']['config']['eval'] = AlternativeTextNotEmpty::class;
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'sys_file_reference',
    [
        'is_decorative' => [
            'exclude' => 1,
            'onChange' => 'reload',
            'label' => 'LLL:EXT:a11y_companion/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.is_decorative',
            'config' => [
                'default' => 0,
                'type' => 'check',
                'items' => [
                    ['LLL:EXT:lang/locallang_core.xlf:labels.enabled', 1],
                ],
            ],
        ],
    ]
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'sys_file_reference',
    'is_decorative'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
    'sys_file_reference',
    'imageoverlayPalette',
    'is_decorative',
    'before:alternative'
);
