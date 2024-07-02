<?php

namespace Taketool\Sysinfo\Service;

use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;

class Mod1Service extends ActionController
{
    public function __construct()
    {}

    public function addDocHeaderButtons(ModuleTemplate $moduleTemplate, UriBuilder $uriBuilder): void
    {
        $iconFactory = GeneralUtility::makeInstance(IconFactory::class);
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
                    'flexform' => 'Mod1:Flexform:actions-list',
                    'deprecation' => 'Mod1:Deprecations:actions-exclamation-triangle',
                    'fileCheck' => 'Mod1:File Check:install-scan-extensions',
                    'syslog' => 'Mod1:Syslog:actions-debug',
                    'securityCheck' => 'Mod1:Security Check:module-security',
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
