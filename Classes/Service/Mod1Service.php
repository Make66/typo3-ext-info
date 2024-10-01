<?php

namespace Taketool\Sysinfo\Service;

use Doctrine\DBAL\Exception;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;

class Mod1Service extends ActionController
{
    public function __construct(
        protected readonly ConnectionPool $connectionPool,
    )
    {}

    /**
     * @throws Exception
     */
    public function getDirsFromSFR(): array
    {
        /*
             * SELECT sf.identifier
                FROM sys_file AS sf
                JOIN sys_file_reference AS sfr
                ON sfr.uid_local = sf.uid
                WHERE sf.identifier NOT LIKE "/typo3%"
                ORDER BY sf.identifier ASC
             */
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('sys_file');
        $result = $queryBuilder
            ->select('sf.identifier', 'sf.storage')
            ->from('sys_file', 'sf')
            ->join(
                'sf',
                'sys_file_reference',
                'sfr',
                $queryBuilder->expr()->eq('sfr.uid_local', $queryBuilder->quoteIdentifier('sf.uid'))
            )
            ->where(
            //$queryBuilder->expr()->notLike('sf.identifier', $queryBuilder->quoteIdentifier('*Typo3*'), '*')
            )
            ->executeQuery()
            ->fetchAllAssociative();
        //DebugUtility::debug($result);
        $all = [];
        foreach($result as $row) {
            $filePath = ($row['storage'] === 1)
                ? '/fileadmin' . $row['identifier']
                : $row['identifier'];
            $filePath = dirname($filePath) . '/';
            if (empty($all[$filePath])) {
                $all[$filePath] = 1;
            } else {
                $all[$filePath] += 1;
            }
        }
        //DebugUtility::debug($all);
        return $all;
    }

    public function addDocHeaderButtons(ModuleTemplate $moduleTemplate, UriBuilder $uriBuilder): void
    {
        $iconFactory = GeneralUtility::makeInstance(IconFactory::class);
        $uriBuilder->reset();
        /*  Valid linkButton conditions are:
            trim($this->getHref()) !== ''
            && trim($this->getTitle()) !== ''
            && $this->getType() === self::class
            && $this->getIcon() !== null
        */
        //$languageService = $this->getLanguageService();
        $buttonBar = $moduleTemplate->getDocHeaderComponent()->getButtonBar();
        foreach([
                    'index' => 'Mod1:Home:actions-house',
                    'user' => 'Mod1:User:status-user-group-backend',
                    'flexform' => 'Mod1:Flexform:actions-list',
                    'deprecation' => 'Mod1:Deprecations:actions-exclamation-triangle',
                    'fileCheck' => 'Mod1:File Check:install-scan-extensions',
                    'syslog' => 'Mod1:Syslog:actions-debug',
                    'securityCheck' => 'Mod1:Security Check:overlay-locked',
                    'shaOne' => 'Sha1:Typo3 SHA1:actions-extension',
                    'plugins' => 'Mod1:Plugins:content-plugin',
                    'rootTemplates' => 'Mod1:Templates:actions-template',
                    'checkDomains' => 'Mod1:robots.txt, sitemap.xml & 404:apps-pagetree-folder-root',
                ] as $action => $param)
        {
            list($controller, $title, $icon) = explode(':', $param);
            $addButton = $buttonBar->makeLinkButton()
                ->setTitle($title)
                ->setShowLabelText($action)
                ->setHref($uriBuilder->uriFor($action,null,$controller))
                ->setIcon($iconFactory->getIcon($icon, Icon::SIZE_SMALL));
            $buttonBar->addButton($addButton);
        }
    }
}