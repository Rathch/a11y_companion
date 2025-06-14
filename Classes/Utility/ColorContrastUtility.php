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

namespace Cru\A11yCompanion\Utility;

class ColorContrastUtility
{
    public static function calculateContrastRatio(string $color1, string $color2): float
    {
        $l1 = self::getLuminance($color1);
        $l2 = self::getLuminance($color2);

        $lightest = max($l1, $l2);
        $darkest = min($l1, $l2);

        return ($lightest + 0.05) / ($darkest + 0.05);
    }

    public static function meetsWCAGAAA(float $ratio): bool
    {
        return $ratio >= 7;
    }

    public static function meetsWCAGAA(float $ratio): bool
    {
        return $ratio >= 4.5;
    }

    private static function getLuminance(string $hexColor): float
    {
        $hex = ltrim($hexColor, '#');
        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;

        $r = $r <= 0.03928 ? $r / 12.92 : pow(($r + 0.055) / 1.055, 2.4);
        $g = $g <= 0.03928 ? $g / 12.92 : pow(($g + 0.055) / 1.055, 2.4);
        $b = $b <= 0.03928 ? $b / 12.92 : pow(($b + 0.055) / 1.055, 2.4);

        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }
}
