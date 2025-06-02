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

        $subQuery = $queryBuilder->getConnection()->createQueryBuilder()
        ->selectLiteral('1')
        ->from('sys_file_reference')
        ->where(
            'sys_file_reference.uid_local = sys_file_metadata.file',
            'sys_file_reference.alternative IS NOT NULL',
            'sys_file_reference.alternative != \'\''
        );

        $result = $queryBuilder
        ->select('sys_file_metadata.*', 'sys_file.*')
        ->from('sys_file_metadata')
        ->innerJoin(
            'sys_file_metadata',
            'sys_file',
            'sys_file',
            $queryBuilder->expr()->eq('sys_file_metadata.file', $queryBuilder->quoteIdentifier('sys_file.uid'))
        )
        ->where(
            $queryBuilder->expr()->or(
                $queryBuilder->expr()->eq('sys_file_metadata.alternative', $queryBuilder->createNamedParameter('')),
                $queryBuilder->expr()->isNull('sys_file_metadata.alternative')
            )
        )
        ->andWhere(
            'NOT EXISTS (' . $subQuery->getSQL() . ')'
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
                $queryBuilder->expr()->or(
                    $queryBuilder->expr()->eq('alternative', $queryBuilder->createNamedParameter('')),
                    $queryBuilder->expr()->isNull('alternative')
                )
            )
            ->executeQuery()
            ->fetchOne();
    }
}
