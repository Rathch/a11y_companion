<?php

// SPDX-FileCopyrightText: 2025 Christian Rath-Ulrich
//
// SPDX-License-Identifier: GPL-3.0-or-later

/*
  * This file is part of the package cru/a11y_companion.
  *
  * Copyright (C) 2024 - 2025 Christian Rath-Ulrich
  *
  * It is free software; you can redistribute it and/or modify it under
  * the terms of the GNU General Public License, either version 3
  * of the License, or any later version.
  *
  * For the full copyright and license information, please read the
  * LICENSE file that was distributed with this source code.
  */

return [
    'tx_a11y_companion_list' => [
        'path' => '/module/system/a11y_companion/list',
        'target' => 'Cru\\A11yCompanion\\Backend\\Controller\\CompanionModuleController::listImagesWithoutAltTextAction',
        'action' => 'listImagesWithoutAltText',
    ],
    'tx_a11y_companion_index' => [
        'path' => '/module/system/a11y_companion/index',
        'target' => 'Cru\\A11yCompanion\\Backend\\Controller\\CompanionModuleController::indexAction',
        'action' => 'index',
    ],
];
