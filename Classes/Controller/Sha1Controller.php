<?php

namespace Taketool\Sysinfo\Controller;

use Psr\Http\Message\ResponseInterface;
use Taketool\Sysinfo\Service\Mod1Service;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Configuration\SiteConfiguration;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class Sha1Controller extends ActionController
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

    public function initializeAction()
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
     * compare all files in public/typo3 against precompiled SHA1 in Resources/Private/SHA1/ (~450kB each)
     * precompiled file generate:
     * find ./typo3 -type f -name "*.php" -exec sha1sum {} \; | gzip > /Users/martin/github/typo3-ext-sysinfo/Resources/Private/SHA1/11.5.39/typo3_files_php.txt.gz
     * find ./typo3 -type f -name "*.js" -exec sha1sum {} \; | gzip > /Users/martin/github/typo3-ext-sysinfo/Resources/Private/SHA1/11.5.39/typo3_files_js.txt.gz

     * a line looks like this: 5964dd3a9fcc9d3141415b1b8511b8938e1aabf0  ./typo3/index.php%
     *
     * @return void
     */
    public function shaOneAction(): ResponseInterface
    {
        $this->view->assignMultiple($this->globalTemplateVars);
        $this->moduleTemplate->setContent($this->view->render());
        return $this->htmlResponse($this->moduleTemplate->renderContent());
    }

    public function shaOneJsAction(): ResponseInterface
    {
        $shaMsg = [];
        $msg = $this->sha1getBaselineFile($baseLineFiles,'js');
        if (count($msg)== 0)
        {
            $shaMsg = $this->sha1compareFiles( $baseLineFiles, 'js');
        }

        $this->view->assignMultiple([
            'msg' => $msg,
            'shaMsg' => $shaMsg,
        ]);
        $this->view->assignMultiple($this->globalTemplateVars);
        $this->moduleTemplate->setContent($this->view->render());
        return $this->htmlResponse($this->moduleTemplate->renderContent());
    }

    public function shaOnePhpAction(): ResponseInterface
    {
        $shaMsg = [];
        $msg = $this->sha1getBaselineFile($baseLineFiles,'php');
        if (count($msg)== 0)
        {
            $shaMsg = $this->sha1compareFiles( $baseLineFiles, 'php');
        }

        $this->view->assignMultiple([
            'msg' => $msg,
            'shaMsg' => $shaMsg,
        ]);
        $this->view->assignMultiple($this->globalTemplateVars);
        $this->moduleTemplate->setContent($this->view->render());
        return $this->htmlResponse($this->moduleTemplate->renderContent());
    }
    /**
     * returns array of messages[$filepath] => message
     *
     * @param $baseLineFiles
     * @param $filetype
     * @return array
     */
    private function sha1compareFiles(&$baseLineFiles, $filetype): array
    {
        // redirect stderr to stdout using 2>&1 to see error messages as well
        $cmd = 'find "' . $this->publicPath . '/typo3" -type "f" -name "*.php" 2>&1'; //
        $msg = [];

        // the following line returns ca. 12.000 filenames and 1.5MB
        exec($cmd, $output, $status);
        foreach ($output as $file) {
            // only process files which type matches
            $fileType = substr($file, strrpos($file,'.') +1);
            if ($fileType != $filetype) continue;
            // does sha1 match?
            $index = '.' . substr($file, strlen($this->publicPath));

            /*
            \nn\t3::debug([
                'file' => $file,
                '$index' => $index,
                'baseLineFiles[$index]' => $baseLineFiles[$index],
                'sha1(file)' => sha1(file_get_contents($file)),
                'key_exists()' => array_key_exists($index, $baseLineFiles),
            ]);
            //die();
            */
            /*
             * case 1: file is not in baseLineFiles => should not be there
             * case 2: file is in baseLineFiles and sha1 does not match => error; message 'file has been altered'
             * case 3: file is in baseLineFiles and sha1 matches => ok, no message
             */
            if (!array_key_exists($index, $baseLineFiles))
            {
                // case 1
                $msg[$index] = 'File should not be here -';
            } else {
                $shaFile = sha1(file_get_contents($file));
                $isSha1match = $shaFile == $baseLineFiles[$index];
                if (!$isSha1match) {
                    // case 2
                    $msg[$index] = 'File altered: '. $shaFile . ':' . $baseLineFiles[$index] . ' ' . $index;
                } else {
                    // case 3
                }
            }
        }
        return $msg;
    }

    /**
     * @param $baseLineFiles
     * @param $fileType
     * @return array
     */
    private function sha1getBaselineFile(&$baseLineFiles, $fileType): array
    {
        // file to open is like /Resources/Private/SHA1/11005030/typo3_files_js.txt
        $msg = [];
        $baseLineFiles = [];
        $gzFile = $this->extPath . '/Resources/Private/SHA1/' . $this->t3version . '/typo3_files_' . $fileType . '.txt.gz';
        //\nn\t3::debug($gzFile);
        $isFile = @file_exists($gzFile);
        //\nn\t3::debug($isFile);
        if (!$isFile)
        {
            $msg[] = 'The file for version ' . $this->t3version . ' is not available: ' . $gzFile;
        } else {
            // ~450KB, unset after needed
            $gz = @file_get_contents($gzFile);
            //\nn\t3::debug($gz);
            if ($gz === false)
            {
                $msg[] = 'Error reading file ' . $gzFile;
            } else {
                // ~1.5MB, unset after needed -> data error exception!
                $gunzip = gzdecode($gz);
                //\nn\t3::debug($gunzip);
                //$this->debug_hexdump($gunzip, 20);
                if ($gunzip === false)
                {
                    $msg[] = 'The input file could not be decoded. Is it a gzip file?';
                } else {
                    $gz = null;
                    unset($gz);
                    // get the lines
                    $gzArray = explode(chr(10), $gunzip);
                    //\nn\t3::debug($gzArray);

                    if ($gzArray === false)
                    {
                        $msg[] = 'gzArray failed to explode!';
                    } else {
                        $gunzip = null;
                        unset($gunzip);

                        // create final array fName => sha1
                        foreach($gzArray as $line)
                        {
                            if (!empty($line)) {
                                $l = explode('  ', $line);
                                $baseLineFiles[$l[1]] = $l[0];
                            }
                        }
                        $gzArray = null;
                        unset($gzArray);
                    }
                }
            }
        }
        return $msg;
    }

}