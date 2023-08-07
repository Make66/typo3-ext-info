<?php

namespace Taketool\Info\ViewHelpers;

use Closure;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class FALViewHelper
 * @package Taketool\Info\ViewHelpers
 */
class FileViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    public function initializeArguments()
    {
        $this->registerArgument('table', 'string', '', false);
        $this->registerArgument('field', 'string', '', true);
        $this->registerArgument('uid', 'integer', '', true);
    }

    public static function renderStatic(array $arguments, Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        /*
        $storage = GeneralUtility::makeInstance(ResourceFactory::class)
            ->getDefaultStorage();
        */
        $table = $arguments['table'] != NULL ? $arguments['table'] : 'tt_content';
        $field = $arguments['field'];
        $uid = intval($arguments['uid']);

        $fileRepository = GeneralUtility::makeInstance(FileRepository::class);
        $fileObjects = $fileRepository->findByRelation($table, $field, $uid);
        //\nn\t3::debug($fileObjects);
        return $fileObjects[0];
    }
}