<?php

namespace Taketool\Sysinfo\Service;

use Doctrine\DBAL\Exception;
use Taketool\Sysinfo\Domain\Model\LogEntry;
use Taketool\Sysinfo\Domain\Repository\LogEntryRepository;
use Taketool\Sysinfo\Utility\SysinfoUtility;
use TYPO3\CMS\Belog\Domain\Model\Constraint;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DeprecationService
{
    public function __construct(
    ){}

    public static function getLog($logFileName=''): array
    {
        $res = [];
        $msg = '';
        $logFiles = [];
        $file2process = $logFileName;
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
            $file2process = ($logFileName == '')
                ? $logFiles[0]
                : $logFileName;
            $res = self::process($logPath . $file2process);
        }

        //\nn\t3::debug($logFiles);

        return [
            'logFile' => $file2process,
            'logFiles' => $logFiles,
            'res' => $res,
            'msg' => (count($logFiles) == 0) ? 'No deprecation log file found.' : '',
        ];
    }

    private static function process($filePath): array
    {
        $res = [];
        $fileRows = file($filePath);
        $hideDeprecations = self::getHide();

        foreach ($fileRows as $row)
        {
            // NOTICE
            if (strpos($row, '[NOTICE]'))
            {
                $t = explode(':', $row);
                $hash = sha1(trim($t[array_key_last($t)]));
                if (!in_array($hash, $hideDeprecations))
                $res[$hash] = [
                    'what' => 'Notice',
                    'issue' => trim($t[array_key_last($t)]),
                    'row' => trim($row),
                ];
            }

            // TCA field
            if (str_contains($row, 'The TCA field'))
            {
                $t = explode('.', $row);
                $hash = sha1(trim($t[array_key_last($t)]));
                if (!in_array($hash, $hideDeprecations))
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
                $hash = sha1(trim($t[array_key_last($t)]));
                if (!in_array($hash, $hideDeprecations))
                $res[sha1(trim($t[array_key_last($t)]))] = [
                    'what' => 'TCA property',
                    'issue' => trim($t[0]),
                    'row' => trim($row),
                ];
            }

        }

        return $res;
    }

    public static function deleteLog(string $logFile): bool
    {
        return @is_file(Environment::getProjectPath() . '/var/log/' . $logFile) && @unlink(Environment::getProjectPath() . '/var/log/' . $logFile);
    }

    public static function hide(string $hash)
    {
        $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);
        $extConf = $extensionConfiguration->get('sysinfo');

        // read ExtConf to array
        $hides = explode(',', $extConf['hideDeprecations']);

        // add hash to ExtConf
        $hides[] = $hash;
        if (empty($hides[0])) unset($hides[0]);

        // write back ExtConf
        $extConf['hideDeprecations'] = implode(',', $hides);
        $extensionConfiguration->set('sysinfo', $extConf);
    }

    public static function getHide(): array
    {
        $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);
        $extConf = $extensionConfiguration->get('sysinfo');

        // read ExtConf to array
        $hides = explode(',', $extConf['hideDeprecations']);
        if (empty($hides[0])) unset($hides[0]);
        return $hides;
    }

    public static function clearHide()
    {
        $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);
        $extConf = $extensionConfiguration->get('sysinfo');

        // write back ExtConf
        $extConf['hideDeprecations'] = '';
        $extensionConfiguration->set('sysinfo', $extConf);
    }

}