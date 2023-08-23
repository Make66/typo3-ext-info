<?php

namespace Taketool\Sysinfo\Controller;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
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

    public function initializeAction()
    {
        $environment = GeneralUtility::makeInstance(Environment::class);
        $this->isComposerMode = $environment->isComposerMode();
        $this->publicPath = $environment->getPublicPath();
        $this->extPath = $environment->getExtensionsPath() . '/' . self::EXTKEY;
        $this->configPath = $this->publicPath . '/typo3conf'; //$environment->getConfigPath();
        $this->t3version = VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version);

        // this does not work on v11 - why?
        //$sysinfoWebPath = PathUtility::getAbsoluteWebPath(ExtensionManagementUtility::extPath(SELF::EXTKEY));
        //$jsCheckPages = $sysinfoWebPath . 'Resources/Public/JavaScript/checkPages.js';

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
     * precompiled file generated gzip(find ./typo3 -type f -exec sha1sum {} \;)
     * a line looks like this: 5964dd3a9fcc9d3141415b1b8511b8938e1aabf0  ./typo3/index.php%
     *
     * @return void
     */
    public function shaOneAction()
    {
        $this->view->assignMultiple($this->globalTemplateVars);
    }

    public function shaOneJsAction()
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
    }

    public function shaOnePhpAction()
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
                            $l = explode('  ', $line);
                            $baseLineFiles[$l[1]] = $l[0];
                        }
                        $gzArray = null;
                        unset($gzArray);
                    }
                }
            }
        }
        return $msg;
    }

    function debug_hexdump($string, $lines=10) {
        if (true) //($_SESSION['debug'] & DEBUG_HEXDUMP)
        {
            $hexdump = '';
            echo '<style>'."\n"
                .' td { font-family: monospace; line-height: 1;}'."\n"
                .'</style>'."\n";
            // hexdump display
            $hexdump .= '<table border="0" cellpadding="0" cellspacing="2" bgcolor="Silver"><tr><td>'."\n";
            $hexdump .= '<table border="0" cellpadding="1" cellspacing="1" bgcolor="White"><tr bgcolor="Silver"><th>0</th><th>1</th><th>2</th><th>3</th><th>4</th><th>5</th><th>6</th><th>7</th><th>&nbsp;</th><th>8</th><th>9</th><th>A</th><th>B</th><th>C</th><th>D</th><th>E</th><th>F</th><th>&nbsp;</th><th>ascii</th></tr>'."\n";
            for ($i=0; $i<$lines; $i++){
                $hexdump .= '<tr>';
                $chrview = "";
                for ($j=0; $j<16; $j++) {
                    $chr = substr($string, $i*16+$j, 1);
                    $asc = ord($chr);
                    $hexdump .= "<td>".bin2hex($chr)."</td>";
                    if ($j==7) { $hexdump .= "<td>&nbsp;</td>"; }
                    if (($asc >31) and ($asc <128)) {
                        $chrview .= chr($asc);
                    } else {
                        $chrview .= ".";
                    }
                }
                $hexdump .= "<td>&nbsp;</td><td>".$chrview."</td>\n";
                $hexdump .= "</tr>";
            }
            $hexdump .= "</table>\n";
            $hexdump .= '</td></tr></table>'."\n";
            echo $hexdump;
        }
    }

}