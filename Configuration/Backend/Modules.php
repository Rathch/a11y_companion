// SPDX-FileCopyrightText: 2025 Christian Rath-Ulrich
//
// SPDX-License-Identifier: GPL-3.0-or-later

<?php

return [
    'a11y_companion' => [
        'parent' => 'system',
        'position' => ['top'],
        'access' => 'user,group',
        'workspaces' => 'live',
        'path' => '/module/system/a11y_companion',
        'labels' => 'LLL:EXT:a11y_companion/Resources/Private/Language/locallang_mod.xlf',
        'extensionName' => 'A11yCompanion',
        'iconIdentifier' => 'module-a11y-companion',
        'routes' => [
            '_default' => [
                'target' => \Cru\A11yCompanion\Backend\Controller\CompanionModuleController::class . '::handleRequest',
            ],
        ],
    ],
];
