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
    public function getConfiguration(): array
    {
        $cacheFile = Environment::getVarPath() . '/cache/data/LinksWithoutPurpose.json';

        if (
            file_exists($cacheFile)
            && filemtime($cacheFile) > time() - 600
        ) {
            $externalLinks = json_decode(file_get_contents($cacheFile), true);
            $count = $this->countLinks($externalLinks);
            return [
                'links' => $externalLinks,
                'count' => $count,
            ];
        }

        $records = $this->fetchRecordsWithLinks();
        $blacklistWords = $this->getBlacklistWords();
        $externalLinks = $this->parseAndFilterLinks($records, $blacklistWords);

        GeneralUtility::writeFileToTypo3tempDir($cacheFile, json_encode($externalLinks));

        $count = $this->countLinks($externalLinks);
        return [
            'links' => $externalLinks,
            'count' => $count,
        ];
    }

    /**
     * Zählt die Gesamtanzahl der Links im Ergebnisarray
     *
     * @param array $externalLinks
     * @return int
     */
    private function countLinks(array $externalLinks): int
    {
        $count = 0;
        foreach ($externalLinks as $linksPerRecord) {
            $count += is_array($linksPerRecord) ? count($linksPerRecord) : 0;
        }
        return $count;
    }

    /**
     * Parsen und Filtern der Links aus den Records
     *
     * @param array $records
     * @param array $blacklistWords
     * @return array
     */
    private function parseAndFilterLinks(array $records, array $blacklistWords): array
    {
        $externalLinks = [];
        foreach ($records as $record) {
            $page = $this->fetchPage($record['pid']);
            $externalLinksForRecord = $this->extractBlacklistedLinks($record, $blacklistWords, $page);
            if (!isset($externalLinksForRecord) || $externalLinksForRecord === [] || $externalLinksForRecord === null) {
                continue;
            }
            $externalLinks[$record['uid']] = $externalLinksForRecord;
        }
        return $externalLinks;
    }

    /**
     * Extrahiert alle Links mit Blacklist-Wörtern aus einem Record
     *
     * @param array $record
     * @param array $blacklistWords
     * @param array $page
     * @return array
     */
    private function extractBlacklistedLinks(array $record, array $blacklistWords, array $page): array
    {
        $links = [];
        $dom = new \DOMDocument();
        @$dom->loadHTML($record['bodytext'], LIBXML_NOERROR);
        $anchors = $dom->getElementsByTagName('a');
        $index = 0;
        foreach ($anchors as $anchor) {
            $linkText = trim($anchor->textContent);
            $href = trim($anchor->getAttribute('href'));
            foreach ($blacklistWords as $blackWord) {
                if (stripos($linkText, $blackWord) !== false) {
                    $links[$index] = [
                        'href' => $href,
                        'content' => $linkText,
                        'uid' => $record['uid'],
                        'pid' => $record['pid'],
                        'title' => $page['title'],
                    ];
                    ++$index;
                    break; // Nur einmal pro Link eintragen
                }
            }
        }
        return $links;
    }

    /**
     * Holt die Seitendaten anhand der PID
     *
     * @param int $pid
     * @return array
     */
    private function fetchPage(int $pid): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
        return $queryBuilder
            ->select('uid', 'title')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq('uid', $pid)
            )
            ->executeQuery()
            ->fetchAssociative() ?? ['uid' => $pid, 'title' => ''];
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
