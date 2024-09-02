<?php

namespace Taketool\Sysinfo\Controller;

use Psr\Http\Message\ResponseInterface;
use Taketool\Sysinfo\Service\Mod1Service;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Configuration\SiteConfiguration;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Site Crawler
 * - check if sites are stable after migration (collect php exceptions)
 * - collect links from content elements to see if they need additional migration
 * using https://github.com/spatie/crawler by Freek Van der Herten <freek@spatie.be">
 */
class CrawlController extends ActionController
{
    const EXTKEY = 'sysinfo';

    protected string $publicPath;
    protected string $configPath;
    protected string $extPath;
    protected string $t3version;
    protected bool $isComposerMode;
    protected array $globalTemplateVars;

    protected $backendUserAuthentication;

    protected ConnectionPool $connectionPool;
    protected Environment $environment;
    protected IconFactory $iconFactory;
    protected Mod1Service $mod1Service;
    protected ModuleTemplateFactory $moduleTemplateFactory;
    protected ModuleTemplate $moduleTemplate;
    protected PageRepository $pageRepository;
    protected SiteConfiguration $siteConfiguration;

    public function __construct(
        ConnectionPool $connectionPool,
        Environment $environment,
        IconFactory $iconFactory,
        Mod1Service $mod1Service,
        ModuleTemplateFactory $moduleTemplateFactory,
        PageRepository $pageRepository,
        SiteConfiguration $siteConfiguration
    )
    {
        $this->backendUserAuthentication = $GLOBALS['BE_USER'];
        $this->connectionPool = $connectionPool;
        $this->environment = $environment;
        $this->iconFactory = $iconFactory;
        $this->mod1Service = $mod1Service;
        $this->moduleTemplateFactory = $moduleTemplateFactory;
        $this->pageRepository = $pageRepository;
        $this->siteConfiguration = $siteConfiguration;
    }

    public function initializeAction(): void
    {
        $this->isComposerMode = $this->environment->isComposerMode();
        $this->publicPath = $this->environment->getPublicPath();
        $this->extPath = $this->environment->getExtensionsPath() . '/' . self::EXTKEY;
        $this->configPath = $this->publicPath . '/typo3conf'; //$environment->getConfigPath();
        $this->t3version = GeneralUtility::makeInstance(Typo3Version::class)->getVersion();
        $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $this->mod1Service->addDocHeaderButtons($this->moduleTemplate, $this->uriBuilder);

        // global template information
        $this->globalTemplateVars = [
            't3version' => $this->t3version,
            'publicPath' => $this->publicPath,
            'isComposerMode' => $this->isComposerMode,
            'memoryLimit' => $memoryLimit = ini_get('memory_limit'),
        ];
    }

    /**
     * @return ResponseInterface
     */
    public function indexAction(): ResponseInterface
    {
        $this->view->assignMultiple($this->globalTemplateVars);
        $this->moduleTemplate->setContent($this->view->render());
        return $this->htmlResponse($this->moduleTemplate->renderContent());
    }

    /**
     * collect all exceptions of a site
     * @return ResponseInterface
     */
    public function exceptionAction(): ResponseInterface
    {


        $this->view->assignMultiple([
        ]);
        $this->view->assignMultiple($this->globalTemplateVars);
        $this->moduleTemplate->setContent($this->view->render());
        return $this->htmlResponse($this->moduleTemplate->renderContent());
    }

    /**
     * collect all links in content of a site
     * @return ResponseInterface
     */
    public function linkAction(): ResponseInterface
    {


        $this->view->assignMultiple([
        ]);
        $this->view->assignMultiple($this->globalTemplateVars);
        $this->moduleTemplate->setContent($this->view->render());
        return $this->htmlResponse($this->moduleTemplate->renderContent());
    }

}