<?php
// SPDX-FileCopyrightText: 2025 Christian Rath-Ulrich
//
// SPDX-License-Identifier: GPL-3.0-or-later

/*
 * This file is part of the package cru/a11y-companion.
 *
 * Copyright (C) 2024 -2025 Christian Rath-Ulrich
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 3
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Cru\A11yCompanion\Evaluation;

use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class AlternativeTextNotEmpty
{
    /**
     * @psalm-suppress PossiblyUnusedMethod
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @throws Exception
     */
    public function evaluateFieldValue(string $value, string $isIn, bool &$set): string
    {
        if ($this->fieldHasA($value)) {
            return $value;
        }

        $this->setFlashMessageForMissingAlternativeText();
        $set = false;

        return '';
    }

    /**
     * @throws Exception
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function setFlashMessageForMissingAlternativeText(): void
    {
        /** @var FlashMessage $message */
        $message = GeneralUtility::makeInstance(
            FlashMessage::class,
            $GLOBALS['LANG']->sL('LLL:EXT:require_alt_text/Resources/Private/Language/locallang.xlf:error.alternativeTextMustNotBeEmpty'),
            '', // header is optional
            ContextualFeedbackSeverity::ERROR,
            true // whether message should be stored in session
        );
        /** @var FlashMessageService $flashMessageService */
        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        $flashMessageService->getMessageQueueByIdentifier()->enqueue($message);
    }

    private function fieldHasA(string $value): bool
    {
        return trim($value) !== '';
    }
}
