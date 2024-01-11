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
     *
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('site', 'string', 'Site Configuration identifier', true);
    }

    /**
     * Returns a URL to link to FormEngine.
     * Link to edit a site: typo3/module/site/configuration/edit
     *      ?token=b9e7c7a701ec311e4820f80d2ce4e3f5e4b58080
     *      &site=bbw
     * Sample: href="/typo3/index.php
     *      ?route=%2Fmodule%2Fsite%2Fconfiguration
     *      &amp;token=5fd7a07b3eb738d853495707ee7605b68df113fd
     *      &amp;action=edit
     *      &amp;site=vintage"
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
        $returnUrl = $uriBuilder->buildUriFromRoute('sysinfo_checkdomains',
            [
                'action' => 'edit',
                'site' => $this->arguments['site'],
            ]);
        // @todo: returnUrl does not yet contain the checkDomain action
        $parameters = GeneralUtility::explodeUrl2Array('&returnUrl=' . urldecode($returnUrl->getQuery()));
        /*
            $parameters =
            array(3 items)
               returnUrl => 'token=e0b561df207fc34acb1b40408e1f0d981936f497' (46 chars)
               action => 'edit' (4 chars)
               site => 'bbw' (3 chars)
         */
        //\nn\t3::debug($parameters);
        $uriFromRoute = $uriBuilder
            ->buildUriFromRoute('site_configuration.edit', $parameters);
        return $uriFromRoute;
    }
}
