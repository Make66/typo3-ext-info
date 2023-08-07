<?php

namespace Taketool\Info\Controller;

use Closure;
use PDO;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

//use TYPO3\CMS\Core\Domain\Repository\PageRepository; // T3v10
use TYPO3\CMS\Core\Exception\Page\PageNotFoundException;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

//use TYPO3\CMS\Frontend\Page\PageRepository;  // T3v9
use TYPO3\CMS\Core\Domain\Repository\PageRepository;

// T3v10
use TYPO3\CMS\Core\Configuration\SiteConfiguration;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2017-2018 Jonathan Heilmann <mail@jonathan-heilmann.de>
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
 * @author      Jonathan Heilmann <mail@jonathan-heilmann.de>
 * @package     Taketool
 * @subpackage  Tool
 */
class Mod1Controller extends ActionController
{
    //https://test.taketool.net/typo3/index.php?route=%2Fmodule%2Ftools%2FInfoM1&token=65325b052ca36756bac2be7f06bd806db2fefaf1&tx_Info_tools_Infom1%5Btype%5D=news_pi1&tx_Info_tools_Infom1%5Baction%5D=plugins&tx_Info_tools_Infom1%5Bcontroller%5D=Mod1
    /**
     * @var QueryBuilder
     */
    protected ConnectionPool $connectionPool;
    protected PageRepository $pageRepository;

    /**
     * @param PageRepository $pageRepository
     */
    public function injectPageRepository(PageRepository $pageRepository)
    {
        $this->pageRepository = $pageRepository;
    }

    /**
     * initialize action
     */
    public function initializeAction()
    {
        $this->connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $this->siteConfiguration = GeneralUtility::makeInstance(SiteConfiguration::class);

    }

    public function securityCheckAction()
    {
        //const $xid =

        // l10n contains directories len!=2
        $environment = GeneralUtility::makeInstance(Environment::class);
        $publicPath = $environment->getPublicPath();
        $localConfPath = $publicPath . '/typo3conf/LocalConfiguration.php';

        //$allDomains = $this->getAllDomains();

        // $GLOBALS['TYPO3_CONF_VARS']['BE']['fileExtensions']['webspace']['allow']
        // $GLOBALS['TYPO3_CONF_VARS']['BE']['fileExtensions']['webspace']['deny']
        // $GLOBALS['TYPO3_CONF_VARS']['BE']['fileDenyPattern'] original or altered?

        // $publicPath/index.php should not be writable: is_writable(string $filename): bool

        // php_errors.log on root
        $isPhpErrorsLogOnRoot = @is_file($publicPath . '/php_errors.log.php');

        // v9: index.php should be a symlink: is_link()

        // v10: index.php.len should be 987bytes or is assumed altered
        $indexSize = @filesize($publicPath . '/index.php');

        // get all .php files on root
        $directoryEntries = [];
        $phpFiles = [];
        $dir = dir($publicPath);
        if ($dir !== false) {
            while (false !== ($entry = $dir->read())) {
                $directoryEntries[] = $entry;
                if (substr($entry, -4) == '.php') {
                    $phpFiles[] = $this->stat($publicPath . '/' . $entry);
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

        // composer: typo3temp should not contain any .php: find ./|grep .php
        // redirect stderr to stdout using 2>&1 to see error messages as well
        $typo3tempPhps = [];
        $cmd = 'find "' . $publicPath . '/typo3temp/" -type "f" -name "*.php" 2>&1';
        exec($cmd, $output, $status);
        foreach ($output as $file) {
            $typo3tempPhps[] = $this->stat($file);
        }
        //\nn\t3::debug($typo3tempPhps);

        $this->view->assignMultiple([
            'fileDenyPattern' => $GLOBALS['TYPO3_CONF_VARS']['BE']['fileDenyPattern'],
            'fileDenyPattern_shouldBe' => '\.(php[3-8]?|phpsh|phtml|pht|phar|shtml|cgi)(\..*)?$|\.pl$|^\.htaccess$',
            'webspace_allow' => $GLOBALS['TYPO3_CONF_VARS']['BE']['fileExtensions']['webspace']['allow'],
            'webspace_deny' => $GLOBALS['TYPO3_CONF_VARS']['BE']['fileExtensions']['webspace']['deny'],
            'publicPath' => $publicPath,
            'unexpectedPhpOnPublicPath' => $notIndexPhpFiles,
            'indexSize' => $indexSize,
            'indexSize_shouldBe' => 987,
            'isPhpErrorsLogOnRoot' => $isPhpErrorsLogOnRoot,
            'phpErrorsPath' => $publicPath . '/php_errors.log',
            'typo3tempPhps' => $typo3tempPhps,

        ]);

        // php files where no php files should be: uploads

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
    }

    /**
     * @param string $file
     * @return void
     * @throws NoSuchArgumentException
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

        // v11: return $this->htmlResponse();
    }

    public function deleteFileAction(string $file = '') // v11: ResponseInterface and no param
    {
        //\nn\t3::debug($file);


        $content = 'File could not be deleted';
        if ($file != '') {
            $content = (@unlink($file))
                ? 'File successfully deleted'
                : 'File could not be deleted';
        }
        clearstatcache();

        $this->view->assign('file', $file);
        $this->view->assign('content', $content);

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
                'stat' => [
                    'owner' => $posixUserInfo['name'],
                    'group' => $posixGroupInfo['name'],
                    'mode' => substr(decoct($stat['mode']), -3, 3),
                    'size' => $stat['size'],
                    'ctime' => $stat['ctime'],
                    'mtime' => $stat['mtime'],
                ],
            ];
        }
    }

    /**
     * returns array of rootPid => https://xxx/
     *
     * @return array
     */
    private function getAllDomains(): array
    {
        $domains = $this->siteConfiguration->getAllExistingSites($useCache = true);
        //\nn\t3::debug($domains);
        $domainUrls = [];
        foreach ($domains as $domain) {
            $robotsTxt = @file_get_contents($domain->getConfiguration()['base'] . '/robots.txt');
            $sitemapXml = @file_get_contents($domain->getConfiguration()['base'] . '/sitemap.xml');
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
            $robotsTxt = @file_get_contents($domain->getConfiguration()['base'] . 'robots.txt');
            $sitemapXml = @file_get_contents($domain->getConfiguration()['base'] . 'sitemap.xml');
            $domainUrls[$domain->getRootPageId()] = [
                'site' => $domain->getIdentifier(),
                'baseUrl' => $domain->getConfiguration()['base'],
                'isRobotsTxt' => $robotsTxt !== false,
                'robotsTxt' => ($robotsTxt !== false) ? $robotsTxt : '',
                'isSitemapXml' => $sitemapXml !== false,
                'sitemapXml' => ($sitemapXml !== false) ? $sitemapXml : '',
            ];
        }
        //\nn\t3::debug($domainUrls);
        return $domainUrls;
    }

    /**
     * @return void
     */
    public function pluginsAction()
    {
        /*
        // Check permission to read config tables
        if (!$GLOBALS['BE_USER']->check('tables_select', 'tx_tool_domain_model_config')) {
            $this->addFlashMessage('Berechtigung für diese Seite fehlt.', '', AbstractMessage::ERROR);
            return;
        }
        */
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
        foreach ($templates as $key => $t) {
            $templates[$key]['pagetitle'] = $this->pageRepository->getPage($t['pid'], $disableGroupAccessCheck = true)['title'];
            $templates[$key]['include_static_file'] = implode('<br>', explode(',', $t['include_static_file']));
        }

        $this->view->assign('templates', $templates);
    }

    public function allTemplatesAction()
    {
        $templates = $this->getAllTemplates('1');
        foreach ($templates as $key => $t) {
            $templates[$key]['pagetitle'] = $this->pageRepository->getPage($t['pid'], $disableGroupAccessCheck = true)['title'];
            $templates[$key]['include_static_file'] = implode('<br>', explode(',', $t['include_static_file']));
        }
        $this->view->assign('templates', $templates);
    }

    public function configSizesAction()
    {
        //$sortBy = ($this->arguments['sortBy'] ==='') ? 'length' : $sortBy;
        $arguments = $this->request->getArguments();
        //debug($arguments);
        if (is_array($arguments)) $sortBy = ($arguments['sortBy']);
        else $sortBy = 'length';
        $query = $this->connectionPool->getQueryBuilderForTable('tx_tool_domain_model_config');
        $res = $query->select('uid', 'pid', 'mandant', 'data')
            ->from('tx_tool_domain_model_config')
            ->where(
                $query->expr()->eq('hidden', 0),
                $query->expr()->eq('deleted', 0)
            )
            ->execute();
        $configs = $res->fetchAll();
        debug(['$configs' => $configs], __line__ . ':' . __function__);
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
    }

    /**
     * @param array $array
     * @param string $key
     * @return array
     */
    private static function sort($array, $key)
    {
        usort($array, self::build_sorter($key));
        return $array;
    }

    /**
     * @param string $key
     * @return Closure
     */
    private static function build_sorter($key)
    {
        return function ($a, $b) use ($key) {
            return strnatcmp($b[$key], $a[$key]);
        };
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
     * @return array
     */
    private function getAllPluginTypes()
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
        foreach ($pT as $key => $p) {
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
        foreach ($pT as $key => $p) {
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
     *
     * @param string $showAll
     * @return array
     */
    private function getAllTemplates($showAll = ''): array
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
}