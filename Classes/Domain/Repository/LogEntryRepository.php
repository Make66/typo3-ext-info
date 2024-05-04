<?php

namespace Taketool\Sysinfo\Domain\Repository;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Exception;
use TYPO3\CMS\Belog\Domain\Model\LogEntry;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Sys log entry repository
 * @internal This class is a TYPO3 Backend implementation and is not considered part of the Public TYPO3 API.
 */
class LogEntryRepository extends \TYPO3\CMS\Belog\Domain\Repository\LogEntryRepository
{

    /**
     * Deletes all messages which have the same message details
     *
     * @param string $uidList
     * @return int
     * @throws Exception
     * @throws DBALException
     */
    public function deleteByUidList(string $uidList): int
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('sys_log');

        return $queryBuilder->delete('sys_log')
            ->where(
                $queryBuilder->expr()->in('uid', $uidList)
            )
            ->executeStatement();
    }

}
