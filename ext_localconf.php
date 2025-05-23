<?php

use Cru\A11yCompanion\Backend\FormDataProvider\RequireAlternativeText;
use Cru\A11yCompanion\Evaluation\AlternativeTextNotEmpty;
use TYPO3\CMS\Backend\Form\FormDataProvider\TcaColumnsProcessPlaceholders;

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals'][AlternativeTextNotEmpty::class] = '';

$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['tcaDatabaseRecord'][RequireAlternativeText::class] = [
    'depends' => [
        TcaColumnsProcessPlaceholders::class
    ]
];

$GLOBALS['TYPO3_CONF_VARS']['SYS']['locallangXMLOverride']['EXT:core/Resources/Private/Language/locallang_tca.xlf'][]
    = 'EXT:require_alt_text/Resources/Private/Language/core_locallang.xlf';