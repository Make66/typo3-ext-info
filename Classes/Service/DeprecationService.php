<?php

namespace Taketool\Sysinfo\Service;

use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
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

    /**
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     */
    private static function process($filePath): array
    {
        $res = [];
        $fileRows = file($filePath);
        $hides = self::getHides();
        foreach ($fileRows as $row)
        {
            // NOTICE
            if (strpos($row, '[NOTICE]'))
            {
                $issue = trim(substr($row, strpos($row, 'TYPO3 Deprecation Notice:')+26));
                $sha1 = sha1($issue);
                if (in_array($sha1, $hides)) continue;

                $res[$sha1] = [
                    'what' => 'Notice',
                    'issue' => trim($issue),
                    'row' => trim($row),
                ];
            }

            // TCA field
            if (str_contains($row, 'The TCA field'))
            {
                $t = explode('.', $row);
                $hash = sha1(trim($t[array_key_last($t)]));
                if (!in_array($hash, $hides))
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
                if (!in_array($hash, $hides))
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
        return @is_file(Environment::getProjectPath() . '/var/log/' . $logFile)
            && @unlink(Environment::getProjectPath() . '/var/log/' . $logFile);
    }

    /**
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     */
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

    /**
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     */
    public static function getHides(): array
    {
        $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);
        $extConf = $extensionConfiguration->get('sysinfo');

        // read ExtConf to array
        $hides = explode(',', $extConf['hideDeprecations']);
        if (empty($hides[0])) unset($hides[0]);
        return $hides;
    }

    public static function clearHide(): void
    {
        $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);
        $extConf = $extensionConfiguration->get('sysinfo');

        // write back ExtConf
        $extConf['hideDeprecations'] = '';
        $extensionConfiguration->set('sysinfo', $extConf);
    }

}