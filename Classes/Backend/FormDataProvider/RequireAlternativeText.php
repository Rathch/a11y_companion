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

namespace Cru\A11yCompanion\Backend\FormDataProvider;

use TYPO3\CMS\Backend\Form\FormDataProviderInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class RequireAlternativeText implements FormDataProviderInterface
{
    /**
     * Add form data to result array.
     *
     * @param array<string, mixed> $result Initialized result array
     *
     * @return array<string, mixed> Result filled with more data
     *
     * @psalm-suppress UnusedVariable
     */
    public function addData(array $result): array
    {
        if (!$this->isImageRelationWithAlternativeText($result)) {
            return $result;
        }

        $configuration = $this->getAlternativeTextFieldConfiguration($result);
        // If a placeholder is present it is used by default if the submitted value is empty,
        // otherwise all related options are removed and the editor is forced to supply a text
        if ($this->placeholderIsEmpty($configuration)) {
            $configuration = $this->removeDefaultAndNullableOptions($configuration);
            $configuration = $this->setRequiredFieldEvaluation($configuration, $result);
            $configuration = $this->removePlaceholderOption($configuration);

            $result = $this->setAlternativeTextFieldConfiguration($configuration, $result);
        }

        return $result;
    }

    private function isImageRelationWithAlternativeText(array $configuration): bool
    {
        return $configuration['tableName'] === 'sys_file_reference'
            && isset($configuration['processedTca']['columns']['alternative']['config']);
    }

    /**
     * @param array<string, mixed> $configuration
     *
     * @return bool
     */
    private function placeholderIsEmpty(mixed $configuration): bool
    {
        return !isset($configuration['placeholder']) || trim($configuration['placeholder']) === '';
    }

    /**
     * @param array<array-key, mixed> $configuration
     *
     * @return array<array-key, mixed>
     */
    private function removeDefaultAndNullableOptions(array $configuration): array
    {
        // Disable NULL values (hides the "Set value" checkbox above input field)
        if (array_key_exists('default', $configuration) && $configuration['default'] === null) {
            unset($configuration['default']);
        }

        if (isset($configuration['nullable']) && $configuration['nullable']) {
            $configuration['nullable'] = false;
        }

        return $configuration;
    }

    /**
     * @param array<string, mixed> $configuration
     *
     * @return array<array-key, mixed>
     */
    private function getAlternativeTextFieldConfiguration(array $configuration): array
    {
        return $configuration['processedTca']['columns']['alternative']['config'];
    }

    /**
     * @param array{eval?: string, required?: bool} $configuration
     * @param array{databaseRow: array{is_decorative?: bool}, processedTca: array, [key: string]: mixed} $result
     *
     * @return array{eval?: string, required?: bool}
     */
    private function setRequiredFieldEvaluation(array $configuration, array $result): array
    {
        $isDecorative = (bool)($result['databaseRow']['is_decorative'] ?? false);
        $evalCodes = GeneralUtility::trimExplode(',', $configuration['eval'] ?? '', true);
        if ($isDecorative) {
            // If the image is decorative, we do not require alternative
            $evalCodes = array_filter($evalCodes, static function ($value): bool {
                return $value !== 'required' && $value !== 'null';
            });
            $configuration['eval'] = implode(',', $evalCodes);
            $configuration['required'] = false;
        } else {
            // If the image is not decorative, we require alternative text
            $evalCodes = array_filter($evalCodes, static function ($value): bool {
                return $value !== 'null' && $value !== 'required';
            });
            $evalCodes[] = 'required';
            $configuration['eval'] = implode(',', $evalCodes);
            $configuration['required'] = true;
        }

        return $configuration;
    }

    /**
     * @param array<array-key, mixed> $configuration
     *
     * @return array<array-key, mixed>
     */
    private function removePlaceholderOption(array $configuration): array
    {
        unset($configuration['placeholder']);
        if (isset($configuration['mode']) && $configuration['mode'] === 'useOrOverridePlaceholder') {
            unset($configuration['mode']);
        }

        return $configuration;
    }

    /**
     * @param array<array-key, mixed> $configuration
     * @param array<array-key, mixed> $result
     *
     * @return array<array-key, mixed>
     */
    private function setAlternativeTextFieldConfiguration(array $configuration, array $result): array
    {
        $result['processedTca']['columns']['alternative']['config'] = $configuration;

        return $result;
    }
}
