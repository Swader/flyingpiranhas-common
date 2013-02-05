<?php

namespace flyingpiranhas\common\http;

use flyingpiranhas\common\http\cookies\Cookie;
use DateTime;
use flyingpiranhas\common\http\cookies\CookieJar;
use flyingpiranhas\common\http\interfaces\RequestInterface;

/**
 * The Request object holds all the request parameters (GET, POST, FILES),
 * the cookies and the server info.
 *
 * @category       http
 * @package        flyingpiranhas.http
 * @license        BSD License
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

    /** @var Params */
    private $oParams;

    /** @var Params */
    private $oServer;

    /** @var CookieJar */
    private $oCookies;

    /**
     * Sets the required values
     */
    public function __construct()
    {
        // build Params objects from $_GET, $_POST and $_FILES
        $this->oParams = new Params(array('GET' => $_GET, 'POST' => $_POST, 'FILES' => $_FILES), 'params');

        // build Params object from $_SERVER
        $this->oServer = new Params($_SERVER, 'server');

        // build Cookies and save them into the CookieRoot object
        $aCookies = array();
        foreach ($_COOKIE as $sName => $mCookie) {
            // the session cookie is a special case as it is not controlled by the framework
            if ($sName != session_name()) {
                $mValue = array();
                parse_str($mCookie, $mValue);

                $dExpDate = (isset($mValue['expires'])) ? (new DateTime)->setTimestamp($mValue['expires']) : null;

                $aCookies[] = new Cookie($sName, $mValue['params'], $dExpDate);
            }
        }
        $this->oCookies = new CookieJar($aCookies);
    }

    /**
     * @return Params
     */
    public function getServer()
    {
        return $this->oServer;
    }

    /**
     * @param string $sType
     *
     * @return Params|null|string
     */
    public function getParams($sType = null)
    {
        if ($sType) {
            return $this->oParams->$sType;
        }
        return $this->oParams;
    }

    /**
     * @return CookieJar
     */
    public function getCookies()
    {
        return $this->oCookies;
    }

}
