<?php

/**
 * Edit Record ViewHelper, see FormEngine logic.
 */
declare(strict_types=1);

namespace Taketool\Info\ViewHelpers\Be;

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
class EditRecordViewHelper extends AbstractViewHelper
{
    /**
     * Init arguments.
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('parameters', 'string', 'A set of GET params to send to FormEngine', true);
        $this->registerArgument('id', 'int', 'Page id', true);
        $this->registerArgument('action', 'string', 'edit|create default to "edit"', false);
    }

    /**
     * Returns a URL to link to FormEngine.
     *
     * @return Uri URL to FormEngine module + parameters
     *
     * @throws RouteNotFoundException
     * @see \TYPO3\CMS\Backend\Utility\BackendUtility::getModuleUrl()
     */
    public function render(): Uri
    {
        //$uriBuilder = GeneralUtility::
        $parameters = $this->arguments['parameters'];
        $action = ($this->arguments['action'] == '')
            ? 'edit'
            : $this->arguments['action'];
        //$returnUrl = BackendUtility::getModuleUrl('web_KurseAdmin', ['id' => $this->arguments['id']]);
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $returnUrl = $uriBuilder->buildUriFromRoute('taketool_ToolBackend', ['id' => $this->arguments['id']]);

        //\nn\t3::debug($returnUrl);

        //$returnUrl = UriBuilder::buildUriFromRoute('web_KurseAdmin', ['id' => $this->arguments['id']]);
        $parameters = GeneralUtility::explodeUrl2Array($parameters . '&returnUrl=' . urldecode($returnUrl->getQuery()));

        //\nn\t3::debug($parameters);

        //return BackendUtility::getModuleUrl('record_'.$action, $parameters);
        return $uriBuilder->buildUriFromRoute('record_' . $action, $parameters);

    }
}
