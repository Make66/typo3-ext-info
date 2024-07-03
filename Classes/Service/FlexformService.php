<?php

namespace Taketool\Sysinfo\Service;

use Doctrine\DBAL\Exception;
use Taketool\Sysinfo\Domain\Model\LogEntry;
use Taketool\Sysinfo\Domain\Repository\LogEntryRepository;
use Taketool\Sysinfo\Utility\SysinfoUtility;
use TYPO3\CMS\Belog\Domain\Model\Constraint;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FlexformService
{
    public function __construct(
    ){}

    public static function getConf(): array
    {
        $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);
        $extConf = $extensionConfiguration->get('sysinfo');

        // read ExtConf to array
        return [
            'ffPrefix' => trim($extConf['ffPrefix']),
            'ffCTypes' => GeneralUtility::trimExplode(',', $extConf['ffCTypes']),
            'ffFields' => GeneralUtility::trimExplode(',', $extConf['ffFields']),
        ];
    }

    public static function getFF(string $cType, array $fields): array
    {
        $extConf = self::getConf();
        $prefix = $extConf['ffPrefix'] . '_';
        $res = [];
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $connectionPool->getQueryBuilderForTable('tt_content');
        $queryBuilder->getRestrictions()->removeAll();
        $rows = $queryBuilder
            ->select('uid', 'pid', 'hidden', 'deleted', 'header', 'pi_flexform') // fields to select
            -> from('tt_content')
            ->where(
                $queryBuilder->expr()->eq('CType', $queryBuilder->createNamedParameter($prefix . $cType))
            )
            ->executeQuery()
            ->fetchAllAssociative();

        foreach ($rows as $key => $row)
        {
            /*
            try {
                $ff = GeneralUtility::xml2array($row['pi_flexform'])['data']['options']['lDEF'];
            } catch (\TypeError) {
                \nn\t3::debug($row);
            }
            */
            if (empty($row['pi_flexform']))
            {
                $res[] = [
                    'uid' => $row['uid'],
                    'pid' => $row['pid'],
                    'hidden' => $row['hidden'],
                    'deleted' => $row['deleted'],
                    'header' => '*** no flexform *** ' . $row['header'],
                    'ff' => [],
                ];
            } else {
                $fieldValues = [];
                $ff = GeneralUtility::xml2array($row['pi_flexform'])['data']['options']['lDEF'];

                // special treatment because of section in DS
                if ($cType == 'article' ||
                    $cType == 'galleryBox' ||
                    $cType == 'downloadBox' ||
                    $cType == 'linkBox') {
                    if ($cType == 'article')
                        list($index, $subIndex, $type, $target) = ['textareas', 'textarea', 'image', 'images'];
                    if ($cType == 'galleryBox')
                        list($index, $subIndex, $type, $target) = ['slides', 'slide', 'image', 'images'];
                    if ($cType == 'downloadBox')
                        list($index, $subIndex, $type, $target) = ['downloads', 'download', 'file', 'files'];
                    if ($cType == 'linkBox')
                        list($index, $subIndex, $type, $target) = ['links', 'link', 'target', 'files'];
                    if (isset($ff[$index]['el'])) {
                        if (is_array($ff[$index]['el']))
                            foreach ($ff[$index]['el'] as $k => $a) {
                                $fieldValues[$target][$k] = $a[$subIndex]['el'][$type]['vDEF'];
                            }
                    }
                }

                foreach ($ff as $k => $a) {
                    if (in_array($k, $fields)) {
                        $fieldValues[$k] = $ff[$k]['vDEF'];
                    }
                }

                $res[] = [
                    'uid' => $row['uid'],
                    'pid' => $row['pid'],
                    'hidden' => $row['hidden'],
                    'deleted' => $row['deleted'],
                    'header' => $row['header'],
                    'ff' => $fieldValues,
                ];
            }
        }

        return $res;
    }

}
