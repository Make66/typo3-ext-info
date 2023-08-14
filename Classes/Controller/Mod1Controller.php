<?php

namespace Taketool\Sysinfo\Controller;

use Closure;
use Doctrine\DBAL\Exception\TableNotFoundException;
use PDO;

//use Psr\Http\Message\ResponseInterface; // v11
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\ConnectionPool;

//use TYPO3\CMS\Core\Database\Query\QueryBuilder;
//use TYPO3\CMS\Frontend\Page\PageRepository;  // T3v9
use TYPO3\CMS\Core\Domain\Repository\PageRepository;

// T3v10
use TYPO3\CMS\Core\Exception\Page\PageNotFoundException;

//use TYPO3\CMS\Core\Messaging\AbstractMessage;
//use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Package\Exception\PackageStatesUnavailableException;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

//use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;

// T3v10
use TYPO3\CMS\Core\Configuration\SiteConfiguration;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2022-2023 Martin Keller <martin.keller@taketool.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 * ************************************************************* */

/**
 * Module for the 'tool' extension.
 *
 * @author      Martin Keller <martin.keller@taketool.de>
 * @package     Taketool
 * @subpackage  Sysinfo
 */
class Mod1Controller extends ActionController
{
    protected ConnectionPool $connectionPool;
    protected PageRepository $pageRepository;
    protected SiteConfiguration $siteConfiguration;
    protected string $publicPath;
    protected string $configPath;
    protected string $t3version;
    protected bool $isComposerMode;
    protected array $globalTemplateVars;

    protected array $hackfiles = [
        'index.php',
        'auto_seo.php',
        'cache.php',
        'wp-blog-header.php',
        'wp-config-sample.php',
        'wp-links-opml.php',
        'wp-login.php',
        'wp-settings.php',
        'wp-trackback.php',
        'wp-activate.php',
        'wp-comments-post.php',
        'wp-cron.php',
        'wp-load.php',
        'wp-mail.php',
        'wp-signup.php',
        'xmlrpc.php',
        'edit-form-advanced.php',
        'link-parse-opml.php',
        'ms-sites.php',
        'options-writing.php',
        'themes.php',
        'admin-ajax.php',
        'edit-form-comment.php',
        'link.php',
        'ms-themes.php',
        'plugin-editor.php',
        'admin-footer.php',
        'edit-link-form.php',
        'load-scripts.php',
        'ms-upgrade-network.php',
        'admin-functions.php',
        'edit.php',
        'load-styles.php',
        'ms-users.php',
        'plugins.php',
        'admin-header.php',
        'edit-tag-form.php',
        'media-new.php',
        'my-sites.php',
        'post-new.php',
        'admin.php',
        'edit-tags.php',
        'media.php',
        'nav-menus.php',
        'post.php',
        'admin-post.php',
        'export.php',
        'media-upload.php',
        'network.php',
        'press-this.php',
        'upload.php',
        'async-upload.php',
        'menu-header.php',
        'options-discussion.php',
        'privacy.php',
        'user-edit.php',
        'menu.php',
        'options-general.php',
        'profile.php',
        'user-new.php',
        'moderation.php',
        'options-head.php',
        'revision.php',
        'users.php',
        'custom-background.php',
        'ms-admin.php',
        'options-media.php',
        'setup-config.php',
        'widgets.php',
        'custom-header.php',
        'ms-delete-site.php',
        'options-permalink.php',
        'term.php',
        'customize.php',
        'link-add.php',
        'ms-edit.php',
        'options.php',
        'edit-comments.php',
        'link-manager.php',
        'ms-options.php',
        'options-reading.php',
        'system_log.php'
    ];

    protected array $falsePositives = [
        '/typo3conf/ext/sysinfo/Classes/Controller/Mod1Controller.php',
    ];
    protected array $fileinfo = [
        '/index.php' => [
            '10004037' => ['size' => 987],
            '11005030' => ['size' => 815],
            ]
    ];

    /**
     * @param PageRepository $pageRepository
     */
    public function injectPageRepository(PageRepository $pageRepository)
    {
        $this->pageRepository = $pageRepository;
    }

    /**
     * initialize action
     * @throws PackageStatesUnavailableException
     */
    public function initializeAction()
    {
        $this->connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $this->siteConfiguration = GeneralUtility::makeInstance(SiteConfiguration::class);
        $environment = GeneralUtility::makeInstance(Environment::class);
        $this->isComposerMode = $environment->isComposerMode();
        $this->publicPath = $environment->getPublicPath();
        $this->configPath = $this->publicPath . '/typo3conf'; //$environment->getConfigPath();
        $this->t3version = VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version);
        $extensionManagementUtility = GeneralUtility::makeInstance(ExtensionManagementUtility::class);

        // global template information
        $this->globalTemplateVars = [
            't3version' => $this->t3version,
            'publicPath' => $this->publicPath,
            'isComposerMode' => $this->isComposerMode,
            'isExtTool' => $extensionManagementUtility::isLoaded('tool'),
        ];
    }

    public function allTemplatesAction()
    {
        $templates = $this->getAllTemplates('1');
        $this->templatesToView($templates);
        $this->view->assignMultiple($this->globalTemplateVars);
    }

    /**
     * only available for private EXT:tool
     * @return void
     */
    public function configSizesAction()
    {
        $arguments = $this->request->getArguments();
        $isTableNotFoundException = false;
        if (is_array($arguments)) $sortBy = $arguments['sortBy'] ?? '';
        else $sortBy = 'length';
        $query = $this->connectionPool->getQueryBuilderForTable('tx_tool_domain_model_config');
        try {
            $res = $query->select('uid', 'pid', 'mandant', 'data')
                ->from('tx_tool_domain_model_config')
                ->where(
                    $query->expr()->eq('hidden', 0),
                    $query->expr()->eq('deleted', 0)
                )
                ->execute();
        } catch (TableNotFoundException $e) {
            //$this->addErrorFlashMessage('<p>Die Tabelle &quot;tx_tool_domain_model_config&quot; wurde nicht gefunden.</p>' . $e->getMessage());
            $this->addFlashMessage($e->getMessage(), 'Die Tabelle "tx_tool_domain_model_config" wurde nicht gefunden.', 2);
            $isTableNotFoundException = true;
        }

        if (!$isTableNotFoundException) {
            $configs = $res->fetchAll();
            //debug(['$configs' => $configs], __line__ . ':' . __function__);
            if (is_array($configs)) foreach ($configs as $key => $conf) {
                $configs[$key]['length'] = strlen($conf['data']);
                $kurse = unserialize($conf['data']);
                $configs[$key]['cntCourses'] = (is_array($kurse)) ? count($kurse) : 0;
                $cntTermine = 0;
                $maxTermine = 0;
                if (is_array($kurse)) foreach ($kurse as $k) {
                    if (is_array($k['termine'])) {
                        $cntTermine += count($k['termine']);
                        $maxTermine = max($maxTermine, count($k['termine']));
                    }
                }
                $configs[$key]['cntTermine'] = $cntTermine;
                $configs[$key]['termineAvgProKurs'] = ($configs[$key]['cntCourses'] > 0) ? intval($cntTermine / $configs[$key]['cntCourses']) : 0;
                $configs[$key]['termineMaxProKurs'] = $maxTermine;
            }
            $configs = self::sort($configs, $sortBy);
            $this->view->assign('configs', $configs);
            $this->view->assign('sortBy', $sortBy);
            $this->view->assignMultiple($this->globalTemplateVars);
        }

    }

    /**
     * from all domains gets all robots.txt and sitemap.xml via https:// (might take long!)
     *
     * @return void
     */
    public function checkDomainsAction()
    {
        $allDomains = $this->getAllDomainsAndExtra();
        //echo '<pre>'; echo serialize($allDomains);echo '</pre>'; die();
        $this->view->assign('allDomains', $allDomains);
        $this->view->assignMultiple($this->globalTemplateVars);
    }

    public function deleteFileAction(string $file = '') // v11: ResponseInterface and no param
    {
        $content = 'File could not be deleted';
        if ($file != '') {
            $content = (@unlink($file))
                ? 'File successfully deleted'
                : 'File could not be deleted';
        }
        clearstatcache();

        $this->view->assign('file', $file);
        $this->view->assign('content', $content);
        $this->view->assignMultiple($this->globalTemplateVars);
        // v11: return $this->htmlResponse();
    }

    /**
     * @return void
     */
    public function pluginsAction()
    {
        $arguments = $this->request->getArguments();
        $type = (isset($arguments['type'])) ? $arguments['type'] : '';
        //DebuggerUtility::var_dump(['$arguments'=>$arguments,'$type'=>$type], __class__.'->'.__function__.'()');

        if ($type == '') {
            $this->view->assign('type', '');
            $this->view->assign('pluginTypes', $this->getAllPluginTypes());
            $this->view->assign('contentTypes', $this->getAllContentTypes());
        } else {
            $this->view->assign('type', $type);
            $this->view->assign('pages4PluginType', $this->getPages4PluginType($type));
            $this->view->assign('pages4ContentType', $this->getPages4ContentType($type));
        }
        $this->view->assignMultiple($this->globalTemplateVars);
    }

    public function postAction()
    {
        $this->view->assignMultiple($this->globalTemplateVars);
    }

    public function rootTemplatesAction()
    {
        /*
        // Check permission to read config tables
        if (!$GLOBALS['BE_USER']->check('tables_select', 'tx_tool_domain_model_config')) {
            $this->addFlashMessage('Berechtigung für diese Seite fehlt.', '', AbstractMessage::ERROR);
            return;
        }
        */
        $arguments = $this->request->getArguments();
        $showAll = (isset($arguments['showAll'])) ? $arguments['showAll'] : '';
        //DebuggerUtility::var_dump(['$arguments'=>$arguments,'showAll'=>$showAll], __class__.'->'.__function__.'()');

        $templates = $this->getAllTemplates();
        $this->templatesToView($templates);
        $this->view->assignMultiple($this->globalTemplateVars);
    }

    public function securityCheckAction()
    {
        $localConfPath = $this->configPath . '/LocalConfiguration.php';

        // $GLOBALS['TYPO3_CONF_VARS']['BE']['fileExtensions']['webspace']['allow']
        // $GLOBALS['TYPO3_CONF_VARS']['BE']['fileExtensions']['webspace']['deny']
        // $GLOBALS['TYPO3_CONF_VARS']['BE']['fileDenyPattern'] original or altered?

        // $this->publicPath/index.php should not be writable: is_writable(string $filename): bool

        // php_errors.log on root
        $isPhpErrorsLogOnRoot = @is_file($this->publicPath . '/php_errors.log.php');

        // non composer v9: index.php should be a symlink: is_link()
        // $this->>isComposerMode

        /*
         * test index on siteroot
         * needs $this->>isComposerMode in template
         * composer: v10: index.php.len should be 987bytes or is assumed altered
         */
        $indexSize = @filesize($this->publicPath . '/index.php');
        $indexSize_shouldBe = $this->fileinfo['/index.php'][$this->t3version]['size'];
        $indexStat = $this->stat($this->publicPath . '/index.php');
        $isIndexSymlink = $indexStat['stat']['nlink'];

        /*
         * test all php on siteroot
         */
        // get all .php files on root
        $phpFiles = [];
        $dir = dir($this->publicPath);
        if ($dir !== false) {
            while (false !== ($entry = $dir->read())) {
                if (substr($entry, -4) == '.php') {
                    $phpFiles[] = $this->stat($this->publicPath . '/' . $entry);
                }
            }
            $dir->close();
        }
        $notIndexPhpFiles = $phpFiles;
        $indexKey = array_search('index.php', array_column($phpFiles, 'entry'));
        array_splice($notIndexPhpFiles, $indexKey);
        //\nn\t3::debug($directoryEntries);
        //\nn\t3::debug($notIndexPhpFiles);
        //die();

        /*
         * test /typo3temp for *.php which should not be there
         */
        // composer: /typo3temp should not contain any .php: find ./|grep .php
        // redirect stderr to stdout using 2>&1 to see error messages as well
        $typo3tempPhps = [];
        $cmd = 'find "' . $this->publicPath . '/typo3temp/" -type "f" -name "*.php" 2>&1';
        exec($cmd, $output, $status);
        foreach ($output as $file) {
            $typo3tempPhps[] = $this->stat($file);
        }
        //\nn\t3::debug($typo3tempPhps);

        /*
         * test /typo3conf, which may contain *.php, for *.php which should not be there
         * use $this->hackfiles to check against
         */
        $typo3confPhps = [];
        $cmd = 'find "' . $this->publicPath . '/typo3conf/" -type "f" -name "*.php" 2>&1';
        exec($cmd, $output, $status);
        foreach ($output as $file) {
            if (in_array(basename($file), $this->hackfiles)) {
                $typo3confPhps[] = $this->stat($file);
            }
        }

        /*
         * test /uploads for php files where no php files should be: uploads (only non composer mode)
         */
        $uploadsPhps = [];
        if (!$this->isComposerMode)
        {
            $cmd = 'find "' . $this->publicPath . '/uploads/" -type "f" -name "*.php" 2>&1';
            exec($cmd, $output, $status);
            foreach ($output as $file) {
                $uploadsPhps[] = $this->stat($file);
            }
            //\nn\t3::debug($uploadsPhps);
        }

        /*
         * test /*.php files and all subdirs which contain suspicious code
         * using basic regular expression
         */
        $searchFor = '';
        $output = '';
        foreach ([
            'error_reporting(0)',
            // too many false-positives'base64_decode(',
            'eval(',
            'gzinflate(',
            'str_rot13(',
        ] as $search) $searchFor .= $search . '\|';
        $searchFor = substr($searchFor, 0, -2);
        $suspiciousPhps = [];
        $cmd = "grep '$searchFor' -rn $this->publicPath --include=*.php  2>&1";
        // status 2 = ERROR
        exec($cmd, $output, $status);
        //\nn\t3::debug([$searchFor,$cmd,$output,$status]);
        foreach ($output as $line) {
            $lineArray= explode(':', $line);
            //\nn\t3::debug($lineArray);
            $file = $lineArray[0];
            if (true) {
                $tmp = [
                    'file' => $this->stat($file),
                    'lnr' => $lineArray[1],
                    'code' => trim($lineArray[2]),
                ];
                // remove from output if false positive
                if (!in_array($tmp['file']['short'], $this->falsePositives)) $suspiciousPhps[] = $tmp;
            }
        }
        //\nn\t3::debug($suspiciousPhps);

        $this->view->assignMultiple([
            'fileDenyPattern' => $GLOBALS['TYPO3_CONF_VARS']['BE']['fileDenyPattern'],
            'isFileDenyPattern' => $GLOBALS['TYPO3_CONF_VARS']['BE']['fileDenyPattern'] != '\.(php[3-8]?|phpsh|phtml|pht|phar|shtml|cgi)(\..*)?$|\.pl$|^\.htaccess$',
            'trustedHostsPattern' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['trustedHostsPattern'],
            'webspace_allow' => $GLOBALS['TYPO3_CONF_VARS']['BE']['fileExtensions']['webspace']['allow'],
            'webspace_deny' => $GLOBALS['TYPO3_CONF_VARS']['BE']['fileExtensions']['webspace']['deny'],
            'indexSize' => $indexSize,
            'indexSize_shouldBe' => $indexSize_shouldBe,
            'isIndexSymlink' => $isIndexSymlink,
            'indexPhp' => $indexStat,
            'phpErrorsPath' => $this->publicPath . '/php_errors.log',
            'isPhpErrors' => $isPhpErrorsLogOnRoot,
            'webrootPhps' => $notIndexPhpFiles,
            'isWebrootPhps' => count($notIndexPhpFiles) > 0,
            'typo3tempPhps' => $typo3tempPhps,
            'isTypo3tempPhps' => count($typo3tempPhps) > 0,
            'typo3confPhps' => $typo3confPhps,
            'isTypo3confPhps' => count($typo3confPhps) > 0,
            'uploadsPhps' => $uploadsPhps,
            'isUploadsPhps' => count($uploadsPhps) > 0,
            'suspiciousPhps' => $suspiciousPhps,
            'isSuspiciousPhps' => count($suspiciousPhps) > 0,
        ]);
        $this->view->assignMultiple($this->globalTemplateVars);
    }

    /**
     * @param string $file
     * @return void
     */
    public function viewFileAction(string $file = '') // v11: ResponseInterface and no param
    {
        //\nn\t3::debug($file);

        /* v11
        if ($this->request->hasArgument('file')) {
            $file = $this->request->getArgument('file');
            //\nn\t3::debug($file);die();
        */
        $content = 'Content could not be loaded';
        if ($file != '') {
            $content = @file_get_contents($file);
        }

        $this->view->assign('file', $file);
        $this->view->assign('content', $content);
        $this->view->assignMultiple($this->globalTemplateVars);
        // v11: return $this->htmlResponse();
    }

    /* v11
        protected function htmlResponse(string $html = null): ResponseInterface
        {
            return $this->responseFactory->createResponse()
                ->withHeader('Content-Type', 'text/html; charset=utf-8')
                ->withBody($this->streamFactory->createStream((string)($html ?? $this->view->render())));
        }
    */


    /**
     * sort arry by certain key
     * @param string $key
     * @return Closure
     */
    private static function build_sorter(string $key): Closure
    {
        return function ($a, $b) use ($key) {
            return strnatcmp($b[$key], $a[$key]);
        };
    }

    /**
     * @return array
     */
    private function getAllContentTypes(): array
    {
        // 1st query: get contentTypes
        $query = $this->connectionPool->getQueryBuilderForTable('tt_content');
        $res = $query->select('*')
            ->from('tt_content')
            ->groupBy('CType')
            ->execute();
        $pT = $res->fetchAll();

        // 2nd query: get count()
        $res = $query->select('*')
            ->from('tt_content')
            ->groupBy('CType')
            ->count('CType')
            ->execute();

        // join the two results into one array
        $i = 0;
        foreach ($res->fetchAll() as $cnt) {
            $pT[$i]['cnt'] = $cnt['COUNT(`CType`)'];
            $i++;
        }
        $contentTypes = [];
        foreach ($pT as $p) {
            if ($p['CType'] == '') continue;
            $contentTypes[] = [
                'CType' => $p['CType'],
                'cnt' => $p['cnt'],
            ];
        }
        //debug(['$query' =>$query, '$res'=>$res, 'pT'=>$pT, '$contentTypes'=>$contentTypes], __line__.':'.__class__.'->'.__function__.'()');
        return $contentTypes;
    }

    /**
     * returns array of rootPid => https://xxx/
     *
     * @return array
     */
    private function getAllDomains(): array
    {
        $domains = $this->siteConfiguration->getAllExistingSites(true);
        //\nn\t3::debug($domains);
        $domainUrls = [];
        foreach ($domains as $domain) {
            //$robotsTxt = @file_get_contents($domain->getConfiguration()['base'] . '/robots.txt');
            //$sitemapXml = @file_get_contents($domain->getConfiguration()['base'] . '/sitemap.xml');
            $domainUrls[$domain->getRootPageId()] = $domain->getConfiguration()['base'];
        }
        return $domainUrls;
    }

    /**
     * returns array of rootPid => https://xxx/, isRobotTxt, robotTxt, isSitemapXml, sitemapXml
     * Attention: takes a while in big installations with many domains!
     *
     * @return array
     */
    private function getAllDomainsAndExtra(): array
    {
        $domains = $this->siteConfiguration->getAllExistingSites($useCache = true);
        $domainUrls = [];
        foreach ($domains as $domain) {

            // we do not need the content, just if the page is readable or not
            $isRobots  = (bool)@fopen($domain->getConfiguration()['base'] . 'robots.txt', 'r');
            $isSitemap = (bool)@fopen($domain->getConfiguration()['base'] . 'sitemap.xml', 'r');
            $is404     = (bool)@fopen($domain->getConfiguration()['base'] . '404', 'r');

            $domainUrls[$domain->getRootPageId()] = [
                'site' => $domain->getIdentifier(),
                'baseUrl' => $domain->getConfiguration()['base'],
                'isRobotsTxt'  => $isRobots,
                'isSitemapXml' => $isSitemap,
                'isPage404'    => $is404,
            ];

            if ($isRobots)  @fclose($domain->getConfiguration()['base'] . 'robots.txt', 'r');
            if ($isSitemap) @fclose($domain->getConfiguration()['base'] . 'sitemap.xml', 'r');
            if ($is404)     @fclose($domain->getConfiguration()['base'] . '404');

        }
        return $domainUrls;
    }

    /**
     * @return array
     */
    private function getAllPluginTypes(): array
    {
        // 1st query: get pluginTypes
        $query = $this->connectionPool->getQueryBuilderForTable('tt_content');
        $res = $query->select('*')
            ->from('tt_content')
            ->groupBy('list_type')
            ->execute();
        $pT = $res->fetchAll();

        // 2nd query: get count()
        $res = $query->select('*')
            ->from('tt_content')
            ->groupBy('list_type')
            ->count('list_type')
            ->execute();
        $i = 0;

        // join the two results into one array
        foreach ($res->fetchAll() as $cnt) {
            $pT[$i]['cnt'] = $cnt['COUNT(`list_type`)'];
            $i++;
        }
        $pluginTypes = [];
        foreach ($pT as $p) {
            if ($p['list_type'] == '') continue;
            $pluginTypes[] = [
                'list_type' => $p['list_type'],
                'cnt' => $p['cnt'],
            ];
        }

        //DebuggerUtility::var_dump(['$type'=>$type, '$query' =>$query, '$res'=>$res, 'pT'=>$pT, '$pluginTypes'=>$pluginTypes], __class__.'->'.__function__.'()');
        return $pluginTypes;
    }

    /**
     *
     * @param string $showAll
     * @return array
     */
    private function getAllTemplates(string $showAll = ''): array
    {
        $query = $this->connectionPool->getQueryBuilderForTable('sys_template');
        if ($showAll == '1') {
            $res = $query->select('*')
                ->from('sys_template')
                ->execute();
        } else {
            $res = $query->select('*')
                ->from('sys_template')
                ->where($query->expr()->eq('root', 1))
                ->execute();
        }
        $templates = $res->fetchAll();
        $pagesOfTemplates = [];
        foreach ($templates as $template) {
            $siteRoot = '- no siteroot -';
            try {
                $rootLineArray = GeneralUtility::makeInstance(RootlineUtility::class, $template['pid'])->get();
                $siteRoot = $rootLineArray[0]['title'];
                unset($rootLineArray[0]);
            } catch (PageNotFoundException $e) {
                // Usually when a page was hidden or disconnected
                // This could be improved by handing in a Context object and decide whether hidden pages
                // Should be linkeable too
                $rootLineArray = [];
            }
            $rLTemp = [];
            foreach ($rootLineArray as $rL) {
                $rLTemp[] = $rL['title'];
            }

            $rootLine = implode('/', array_reverse($rLTemp));
            $pagesOfTemplates[] = [
                'uid' => $template['uid'],
                'pid' => $template['pid'],
                'siteroot' => $siteRoot,
                'rootline' => $rootLine,
                'include_static_file' => $template['include_static_file']
            ];
        }
        //DebuggerUtility::var_dump(['$query' =>$query, '$res'=>$res, '$templates'=>$templates,'$pagesOfTemplates'=>$pagesOfTemplates], 'getAllTemplates()');
        return $pagesOfTemplates;
    }

    /**
     * @param string
     * @return array
     */
    private function getPages4ContentType($type): array
    {
        $query = $this->connectionPool->getQueryBuilderForTable('tt_content');
        $res = $query->select('*')
            ->from('tt_content')
            ->where($query->expr()->eq('CType', $query->createNamedParameter($type)))
            ->execute();
        $plugins = $res->fetchAll();

        // we need uid and pid and page rootpath
        $pagesOfContentType = [];
        $rootLineArray = [];
        foreach ($plugins as $plugin) {
            try {
                $rootLineArray = GeneralUtility::makeInstance(RootlineUtility::class, $plugin['pid'])->get();
            } catch (PageNotFoundException $e) {
                // Usually when a page was hidden or disconnected
                // This could be improved by handing in a Context object and decide whether hidden pages
                // Should be linkeable too
                $rootLine = [];
            }
            $siteRoot = $rootLineArray[0]['title'];
            unset($rootLineArray[0]);
            $rLTemp = [];
            foreach ($rootLineArray as $rL) {
                $rLTemp[] = $rL['title'];
            }

            $rootLine = implode('/', array_reverse($rLTemp));
            $pagesOfContentType[] = [
                'uid' => $plugin['uid'],
                'pid' => $plugin['pid'],
                'hidden' => $plugin['hidden'],
                'deleted' => $plugin['deleted'],
                'siteroot' => $siteRoot,
                'rootline' => $rootLine,
            ];
        }
        //DebuggerUtility::var_dump(['$type'=>$type, '$pagesOfContentType'=>$pagesOfContentType], __class__.'->'.__function__.'()');
        return $pagesOfContentType;
    }

    /**
     * @param string
     * @return array
     */
    private function getPages4PluginType($type): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tt_content');
        $queryBuilder->getRestrictions()
            ->removeAll();
        $res = $queryBuilder
            ->select('c.uid', 'c.pid', 'c.hidden as cHidden', 'c.deleted as cDeleted',
                'p.title as pTitle', 'p.hidden as pHidden', 'p.deleted as pDeleted')
            ->from('tt_content', 'c')
            ->join(
                'c',
                'pages',
                'p',
                $queryBuilder->expr()->eq('p.uid', $queryBuilder->quoteIdentifier('c.pid'))
            )
            ->where(
                $queryBuilder->expr()->eq('list_type', $queryBuilder->createNamedParameter($type, PDO::PARAM_STR))
            )
            ->execute();
        $plugins = $res->fetchAll();

        /*
        $query = $this->connectionPool->getQueryBuilderForTable('tt_content');
        $res = $query->select('uid', 'pid')
            ->from('tt_content')
            ->where($query->expr()->eq('list_type', $query->createNamedParameter($type)))
            ->execute();
        $plugins = $res->fetchAll();
        */

        // we need uid and pid and page rootpath
        $pagesOfPluginType = [];
        $rootLineArray = [];
        foreach ($plugins as $plugin) {
            try {
                $rootLineArray = GeneralUtility::makeInstance(RootlineUtility::class, $plugin['pid'])->get();
            } catch (PageNotFoundException $e) {
                // Usually when a page was hidden or disconnected
                // This could be improved by handing in a Context object and decide whether hidden pages
                // Should be linkeable too
                $rootLine = [];
            }
            $siteRoot = $rootLineArray[0]['title'];
            unset($rootLineArray[0]);

            $rLTemp = [];
            foreach ($rootLineArray as $rL) {
                $rLTemp[] = $rL['title'];
            }

            $rootLine = implode('/', array_reverse($rLTemp));
            $pagesOfPluginType[] = [
                'uid' => $plugin['uid'],
                'pid' => $plugin['pid'],
                'cHidden' => $plugin['cHidden'],
                'cDeleted' => $plugin['cDeleted'],
                'pHidden' => $plugin['pHidden'],
                'pDeleted' => $plugin['pDeleted'],
                'pTitle' => $plugin['pTitle'],
                'siteroot' => $siteRoot,
                'rootline' => $rootLine,
            ];
        }

        //DebuggerUtility::var_dump(['$type'=>$type, '$pagesOfPluginType'=>$pagesOfPluginType], __class__.'->'.__function__.'()');
        return $pagesOfPluginType;
    }

    /**
     * @param array $array
     * @param string $key
     * @return array
     */
    private static function sort(array $array, string $key): array
    {
        usort($array, self::build_sorter($key));
        return $array;
    }

    /**
     * returns an array of file=>fullpath,stat=stat(fullpath)[uid,gid,mode,size,ctime,mtime]
     *
     * @param string $fullpath
     * @return array
     */
    private function stat(string $fullpath): array
    {
        clearstatcache();
        $stat = @stat($fullpath);

        if (!$stat) {
            return [
                'file' => '[cannot stat file]',
                'stat' => [
                    'uid' => '',
                    'gid' => '',
                    'mode' => '',
                    'nlink' => false,
                    'size' => '',
                    'ctime' => '',
                    'mtime' => '',
                ],
            ];
        } else {
            $posixUserInfo = @posix_getpwuid($stat['uid']);
            $posixGroupInfo = @posix_getgrgid($stat['gid']);
            //\nn\t3::debug($posixUserInfo);
            //\nn\t3::debug($posixGroupInfo);
            return [
                'file' => $fullpath,
                'short' => substr($fullpath, strlen($this->publicPath)),
                'stat' => [
                    'owner' => $posixUserInfo['name'],
                    'group' => $posixGroupInfo['name'],
                    'ow_gr' => $posixUserInfo['name'] . ':' . $posixGroupInfo['name'],
                    'mode' => substr(decoct($stat['mode']), -3, 3),
                    'nlink' => $stat['nlink'] >0,
                    'size' => $stat['size'],
                    'ctime' => $stat['ctime'],
                    'mtime' => $stat['mtime'],
                ],
            ];
        }
    }

    /**
     * @param array $templates
     * @return void
     */
    private function templatesToView(array $templates): void
    {
        foreach ($templates as $key => $t) {
            $templates[$key]['pagetitle'] = $this->pageRepository->getPage($t['pid'], $disableGroupAccessCheck = true)['title'];
            $templates[$key]['include_static_file'] = implode('<br>', explode(',', $t['include_static_file']));
        }
        $this->view->assign('templates', $templates);
    }

}