<?php
declare(strict_types = 1);

namespace Taketool\Sysinfo\Controller;

use InvalidArgumentException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
//use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

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
 * Module for the 'sysinfo' extension.
 *
 * @author      Martin Keller <martin.keller@taketool.de>
 * @package     Taketool
 * @subpackage  Sysinfo
 */
class CurlController //extends ActionController
{
    /** @var ResponseFactoryInterface */
    private $responseFactory;

    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    /**
     * finds out if a remote file exists or not
     * returns a response with result = {site: $site, type: $type, res: true|false}
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function indexAction(ServerRequestInterface $request): ResponseInterface
    {
        $file = $request->getQueryParams()['file'] ?? null;
        $site = $request->getQueryParams()['site'] ?? null;
        $type = $request->getQueryParams()['type'] ?? null;
        if ($file === null) {
            throw new InvalidArgumentException('Please provide a complete domain', 1580585108);}
        if ($site === null) {
            throw new InvalidArgumentException('Please provide a site identifier', 1580585109);}
        if ($type=== null) {
            throw new InvalidArgumentException('Please provide a file type', 1580585110);}

        $data = ['result' => [
            'site' => $site,
            'type' => $type,
            'res' => $this->remoteFileExists($file),
        ]];

        $response = $this->responseFactory->createResponse()
            ->withHeader('Content-Type', 'application/json; charset=utf-8');
        $response->getBody()->write(json_encode($data));
        return $response;
    }

    /**
     * waits 2 sec for the url to answer
     * and accepts everything HTTP < 400 as success
     *
     * @param $url
     * @return bool
     */
    private function remoteFileExists($url): bool
    {
        //don't fetch the actual page, you only want to check the connection is ok
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 2);
        $result = curl_exec($curl);
        $ret = false;

        if ($result !== false) {
            //if request was ok, check response code
            if (curl_getinfo($curl, CURLINFO_RESPONSE_CODE) < 400) {
                $ret = true;
            }
            $this->log($url, ($ret)? 'found' : 'miss');
        } else {
            $this->log($url, 'false');
        }
        curl_close($curl);
        return $ret;
    }

    private function log($url, $str)
    {
        fwrite(
            fopen($_SERVER['DOCUMENT_ROOT'] . '/curl.log', 'a'),
            date('Y-m-d H:i:s') . ' ' . $url .' - '.$str . "\r\n"
        );
    }

}