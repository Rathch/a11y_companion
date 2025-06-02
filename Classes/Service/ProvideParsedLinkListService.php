<?php

// SPDX-FileCopyrightText: 2025 Christian Rath-Ulrich
//
// SPDX-License-Identifier: GPL-3.0-or-later

/*
  * This file is part of the package cru/A11yCompanion.
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

declare(strict_types=1);

namespace Cru\A11yCompanion\Service;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final readonly class ProvideParsedLinkListService
{
    public function getConfiguration(bool $useCache = true): array
    {
        $cacheFile = Environment::getVarPath() . '/cache/data/LinksWithoutPurpose.json';
        $externalLinks = [];

        if (
            $useCache === true
            && file_exists($cacheFile)
            && filemtime($cacheFile) > time() - 600
        ) {
            return json_decode(file_get_contents($cacheFile), true);
        }

        $records = $this->fetchRecordsWithLinks();
        $blacklistWords = $this->getBlacklistWords();
        foreach ($records as $record) {
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
            $page = $queryBuilder
            ->select('uid', 'title')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq('uid', $record['pid'])
            )
            ->executeQuery()
            ->fetchAssociative();

            $dom = new \DOMDocument();
            @$dom->loadHTML($record['bodytext'], LIBXML_NOERROR);
            $anchors = $dom->getElementsByTagName('a');
            $index = 0;
            foreach ($anchors as $anchor) {
                $linkText = trim($anchor->textContent);
                $href = trim($anchor->getAttribute('href'));
                foreach ($blacklistWords as $blackWord) {
                    if (stripos($linkText, $blackWord) !== false) {
                        $externalLinks[$record['uid']][$index] = [
                            'href' => $href,
                            'content' => $linkText,
                            'uid' => $record['uid'],
                            'pid' => $record['pid'],
                            'title' => $page['title'],
                        ];
                        $index++;
                    }
                }
            }
        }

        GeneralUtility::writeFileToTypo3tempDir($cacheFile, json_encode($externalLinks));

        return $externalLinks;
    }

    private function fetchRecordsWithLinks(): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
        $records = $queryBuilder
            ->select('uid', 'bodytext', 'pid')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->like('bodytext', $queryBuilder->createNamedParameter('%<a%'))
            )
            ->executeQuery()
            ->fetchAllAssociative();

        return $records;
    }

    /**
     * Fetches all blacklist words from the database table tx_a11ycompanion_blacklistword
     *
     * @return string[]
     */
    private function getBlacklistWords(): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_a11ycompanion_blacklistword');
        $rows = $queryBuilder
            ->select('word')
            ->from('tx_a11ycompanion_blacklistword')
            ->where(
                $queryBuilder->expr()->eq('hidden', 0),
                $queryBuilder->expr()->eq('deleted', 0)
            )
            ->executeQuery()
            ->fetchAllAssociative();
        return array_column($rows, 'word');
    }
}
