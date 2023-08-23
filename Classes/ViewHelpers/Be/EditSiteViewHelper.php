<?php

/**
 * Edit Record ViewHelper, see FormEngine logic.
 */
declare(strict_types=1);

namespace Taketool\Sysinfo\ViewHelpers\Be;

use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Edit Record ViewHelper, see FormEngine logic.
 *
 * @internal
 */
class EditSiteViewHelper extends AbstractViewHelper
{
    /**
     * Init arguments.
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('site', 'string', 'Site Configuration identifier', true);
    }

    /**
     * Returns a URL to link to FormEngine.
     * Sample: href="/typo3/index.php?route=%2Fmodule%2Fsite%2Fconfiguration&amp;token=5fd7a07b3eb738d853495707ee7605b68df113fd&amp;action=edit&amp;site=vintage"
     * + returnUrl
     *
     * @return Uri URL to FormEngine module + parameters
     *
     * @throws RouteNotFoundException
     * @see \TYPO3\CMS\Backend\Utility\BackendUtility::getModuleUrl()
     */
    public function render(): Uri
    {
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $returnUrl = $uriBuilder->buildUriFromRoute('tools_SysinfoM1',
            [
                'action' => 'edit',
                'site' => $this->arguments['site'],
            ]);
        $parameters = GeneralUtility::explodeUrl2Array('&returnUrl=' . urldecode($returnUrl->getQuery()));
        /*
            $parameters =
            array(3 items)
               returnUrl => 'route=/module/tools/InfoM1' (36 chars)
               token => 'e5a7d1d3a45229de1aafc4162cac22dbe656e8e8' (40 chars)
               site => 'vintage' (7 chars)
         */
        //\nn\t3::debug($parameters);
        $uriFromRoute = $uriBuilder
            ->buildUriFromRoute('site_configuration', $parameters);
        return $uriFromRoute;
    }
}
