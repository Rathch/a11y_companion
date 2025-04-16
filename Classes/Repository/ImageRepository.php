<?php

declare(strict_types=1);

namespace Cru\A11yCompanion\Repository;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ImageRepository
{
    public function findImagesWithoutAltText(int $offset = 0, int $limit = 10): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_file_metadata');

        $result = $queryBuilder
            ->select('sys_file_metadata.*', 'sys_file.*')
            ->from('sys_file_metadata')
            ->join(
                'sys_file_metadata',
                'sys_file',
                'sys_file',
                $queryBuilder->expr()->eq('sys_file_metadata.file', 'sys_file.uid')
            )
            ->where(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->eq('sys_file_metadata.alternative', $queryBuilder->createNamedParameter('')),
                    $queryBuilder->expr()->isNull('sys_file_metadata.alternative')
                )
            )
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->executeQuery()
            ->fetchAllAssociative();
        return $result;
    }

    public function countImagesWithoutAltText(): int
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_file_metadata');
        return (int)$queryBuilder
            ->count('*')
            ->from('sys_file_metadata')
            ->where(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->eq('alternative', $queryBuilder->createNamedParameter('')),
                    $queryBuilder->expr()->isNull('alternative')
                )
            )
            ->executeQuery()
            ->fetchOne();
    }
}