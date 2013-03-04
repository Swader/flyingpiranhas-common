<?php

namespace flyingpiranhas\common\http;

use flyingpiranhas\common\http\cookies\Cookie;
use DateTime;
use flyingpiranhas\common\http\interfaces\RequestInterface;

/**
 * The Request object holds all the request parameters (GET, POST, FILES),
 * the cookies and the server info.
 *
 * @category       http
 * @package        flyingpiranhas.http
 * @license        Apache-2.0
 * @version        0.01
 * @since          2012-09-07
 * @author         Ivan Pintar
 */
class Request implements RequestInterface
{

    const REQUEST_METHOD_GET = 'GET';
    const REQUEST_METHOD_POST = 'POST';
    const REQUEST_METHOD_PUT = 'PUT';
    const REQUEST_METHOD_DELETE = 'DELETE';

    const PARAM_TYPES_GET = 'GET';
    const PARAM_TYPES_POST = 'POST';
    const PARAM_TYPES_FILES = 'FILES';

    /** @var array */
    private $aParams = array();

    /** @var array */
    private $aServer = array();

    /** @var array */
    private $aCookies;

    /**
     * Sets the required values
     */
    public function __construct()
    {
        // build params arrays from $_GET, $_POST and $_FILES
        $this->aParams = array('GET' => $_GET, 'POST' => $_POST, 'FILES' => $_FILES);

        // build server array from $_SERVER
        $this->aServer = $_SERVER;

        // build Cookies and save them into the CookieRoot object
        $aCookies = array();
        foreach ($_COOKIE as $sName => $mCookieVal) {
            $mValue = json_decode($mCookieVal, true);
            if ($mValue === null) {
                $mValue = $mCookieVal;
            }

            if (!(isset($mValue[Cookie::EXPIRY_KEY]) && isset($mValue[Cookie::VALUES_KEY]))) {
                $aCookies[$sName] = new Cookie($sName, $mValue);
                continue;
            }

            $dExpDate = (new DateTime)->setTimestamp($mValue[Cookie::EXPIRY_KEY]);
            $aCookies[$sName] = new Cookie($sName, $mValue[Cookie::VALUES_KEY], $dExpDate);
        }
        $this->aCookies = $aCookies;
    }

    /**
     * @return array
     */
    public function getServer()
    {
        return $this->aServer;
    }

    /**
     * @param string $sRequestMethod
     *
     * @return array|null|string
     */
    public function getParams($sRequestMethod = null)
    {
        if ($sRequestMethod) {
            return $this->aParams[$sRequestMethod];
        }
        return $this->aParams;
    }

    /**
     * @return array
     */
    public function getCookies()
    {
        return $this->aCookies;
    }

}
