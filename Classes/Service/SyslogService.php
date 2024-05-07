<?php

namespace Taketool\Sysinfo\Service;

use Doctrine\DBAL\Exception;
use Taketool\Sysinfo\Domain\Model\LogEntry;
use Taketool\Sysinfo\Domain\Repository\LogEntryRepository;
use Taketool\Sysinfo\Utility\SysinfoUtility;
use TYPO3\CMS\Belog\Domain\Model\Constraint;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SyslogService
{
    protected $logEntryRepository;

    public function __construct(
        LogEntryRepository $logEntryRepository
    )
    {
        $this->logEntryRepository = $logEntryRepository;
    }

    public function getLog(int $logType): array
    {
        // Fetch logs
        $rawLogs = $this->logEntryRepository->findByConstraint($this->getSyslogConstraint());

        //deliver only <max> +1 entries
        $max = 19;

        $logs = [];
        $msg = '';
        $cntErrors = 0;
        $cntErrorsShown = 0;
        $logsCount[0] = 0;
        $logsCount[1] = 0;
        $logsCount[2] = 0;
        $logsCount[3] = 0;

        // If no logs were found, we don't need to continue
        if (($cntLogs = count($rawLogs)) > 0) {
            // Filter for errors, because the LogRepo cannot filter them in advance
            $logsByType[0] = array_filter($rawLogs->toArray(), function (LogEntry $log) {
                return $log->getError() == 0;
            });
            $logsCount[0] = count($logsByType[0]);

            $logsByType[1] = array_filter($rawLogs->toArray(), function (LogEntry $log) {
                return $log->getError() == 1;
            });
            $logsCount[1] = count($logsByType[1]);

            $logsByType[2] = array_filter($rawLogs->toArray(), function (LogEntry $log) {
                return $log->getError() == 2;
            });
            $logsCount[2] = count($logsByType[2]);

            $logsByType[3] = array_filter($rawLogs->toArray(), function (LogEntry $log) {
                return $log->getError() == 3;
            });
            $logsCount[3] = count($logsByType[3]);

            if (($cntErrors = count($logsByType[$logType])) > 0) {

                // collect all errors to hash => errorDetails[cnt, detail, uidList]
                $res = [];
                foreach ($logsByType[$logType] as $log)
                {
                    $detail = $log->getDetails();
                    $hash = hash('md5', $detail);
                    // first error of this kind
                    if (empty($res[$hash])) {
                        $res[$hash]['cnt'] = 1;
                        $res[$hash]['detail'] = $detail;
                        // subsequent errors of this kind
                    } else {
                        $res[$hash]['cnt'] += 1;
                    }
                    $res[$hash]['uidList'][] = $log->getUid();
                    $res[$hash]['ts'] = $log->getTstamp();
                }
                $res = SysinfoUtility::sortReverse($res, 'cnt');

                // deliver only <max> +1 entries
                $cnt = 0;
                foreach ($res as $r)
                {
                    $cntErrorsShown += $r['cnt'];
                    $r['uidList'] = implode(',', $r['uidList']);
                    $logs[] = $r;
                    if ($cnt++ >= $max) break;
                }
            } else $msg = 'No error logs after filtering available.';
        } else $msg = 'No error logs available.';

        return [
            'cntErrors' => $cntErrors,
            'cntErrorsShown' => $cntErrorsShown,
            'cntLogs' => $cntLogs,
            'logs' => $logs,
            'msg' => $msg,
            'logsCount' => $logsCount,
        ];
    }

    public function deleteByUidList($uidList): int
    {
       return $this->logEntryRepository->deleteByUidList($uidList);
    }

    protected function getSyslogConstraint(): Constraint
    {
        /** @var Constraint $constraint */
        $constraint = GeneralUtility::makeInstance(Constraint::class);
        $constraint->setStartTimestamp(0); // Output all reports for test purposes (but will be limited again, so don't worry)
        $constraint->setNumber(10000);
        $constraint->setEndTimestamp(time());
        return $constraint;
    }

}