<?php
declare(strict_types=1);


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'sys_file_metadata',
    [
        'is_decorative' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:a11y_companion/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.is_decorative',
            'config' => [
                'type' => 'check',
                'items' => [
                    ['LLL:EXT:lang/locallang_core.xlf:labels.enabled', 1]
                ]
            ],  
        ],
    ]
 );

 \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'sys_file_metadata',
    'is_decorative'
 );
 \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
    'sys_file_metadata',
    'imageoverlayPalette',
    'is_decorative',
    'before:alternative'
 );