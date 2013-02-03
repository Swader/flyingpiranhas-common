<?php

namespace flyingpiranhas\common\http\interfaces;

use flyingpiranhas\common\http\interfaces\ContentInterface;

/**
 * A Response object that is expected to be used by other FP components
 * should implement this interface.
 *
 * @category       http
 * @package        flyingpiranhas.http
 * @license        BSD License
 * @version        0.01
 * @since          2012-09-07
 * @author         Ivan Pintar
 */
interface ResponseInterface
{

    /**
     * @param ContentInterface|string $mContent
     */
    public function setContent($mContent);

    /**
     * @param string $sUrl
     * @param int    $iRedirectHeader
     */
    public function redirect($sUrl, $iRedirectHeader = 302);

    /**
     * @param RequestInterface $oRequest
     */
    public function redirectToReferrer(RequestInterface $oRequest);

    /**
     * @param array $aHeaders
     */
    public function addHeaders(array $aHeaders);

    /**
     * Adds a single header to the end of the headers array
     *
     * @param string $sHeader
     *
     * @return ResponseInterface
     */
    public function addHeader($sHeader);

    public function clearHeaders();

    public function send();

}