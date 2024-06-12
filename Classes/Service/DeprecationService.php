<?php

namespace Taketool\Sysinfo\Service;

use Doctrine\DBAL\Exception;
use Taketool\Sysinfo\Domain\Model\LogEntry;
use Taketool\Sysinfo\Domain\Repository\LogEntryRepository;
use Taketool\Sysinfo\Utility\SysinfoUtility;
use TYPO3\CMS\Belog\Domain\Model\Constraint;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DeprecationService
{
    public function __construct(
    ){}

    public static function getLog($logFileName=''): array
    {
        $res = [];
        $logFiles = [];
        $logPath = Environment::getProjectPath() . '/var/log/';

        // find log file(s)
        $dir = dir($logPath);
        if ($dir !== false) {
            while (false !== ($entry = $dir->read())) {
                if (str_contains($entry, 'typo3_deprecations_')) {
                    $logFiles[] = $entry;
                }
            }
            $dir->close();
        }

        // Fetch logs
        if (count($logFiles) == 1 || $logFileName != '')
        {
            $res = ($logFileName == '')
                ? self::process($logPath.$logFiles[0])
                : self::process($logPath.$logFileName);
        }

        //\nn\t3::debug($logFiles);

        return [
            'logFiles' => $logFiles,
            'res' => $res,
        ];
    }

    private static function process($filePath)
    {
        $res = [];
        $fileRows = file($filePath);
        foreach ($fileRows as $row)
        {
            // NOTICE
            if (strpos($row, '[NOTICE]'))
            {
                $t = explode(':', $row);
                $res[sha1(trim($t[array_key_last($t)]))] = [
                    'what' => 'Notice',
                    'issue' => trim($t[array_key_last($t)]),
                    'row' => trim($row),
                ];
            }

            // TCA field
            if (str_contains($row, 'The TCA field'))
            {
                $t = explode('.', $row);
                $res[sha1(trim($t[array_key_last($t)]))] = [
                    'what' => 'TCA field',
                    'issue' => trim($t[0]),
                    'row' => trim($row),
                ];
            }

            // TCA property
            if (str_contains($row, 'The TCA property'))
            {
                $t = explode('.', $row);
                $res[sha1(trim($t[array_key_last($t)]))] = [
                    'what' => 'TCA property',
                    'issue' => trim($t[0]),
                    'row' => trim($row),
                ];
            }

        }

        return $res;
    }

    /**
     * @throws Exception
     */
    public function deleteByUidList(string $uidList): int
    {
        return $this->logEntryRepository->deleteByUidList($uidList);
    }

    /**
     * @throws Exception
     */
    public function deleteByLogType(int $logType): int
    {
        return $this->logEntryRepository->deleteByLogType($logType);
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