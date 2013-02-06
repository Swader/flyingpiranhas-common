<?php

namespace flyingpiranhas\common\http;
use flyingpiranhas\common\http\cookies\Cookie;
use flyingpiranhas\common\http\interfaces\ContentInterface;
use flyingpiranhas\common\http\interfaces\RequestInterface;
use flyingpiranhas\common\http\interfaces\ResponseInterface;
use InvalidArgumentException;

/**
 * The Response object is used to set and send various http headers to the client.
 * Redirection and sending cookies is handled here.
 *
 * @category       http
 * @package        flyingpiranhas.http
 * @license        Apache-2.0
 * @version        0.01
 * @since          2012-09-07
 * @author         Ivan Pintar
 */
class Response implements ResponseInterface
{

    const VERSION_11 = 'HTTP/1.1';
    const VERSION_10 = 'HTTP/1.0';

    /** @var string */
    private $sVersion = 'HTTP/1.1';

    /** @var mixed */
    private $mContent;

    /** @var array */
    private $aHeaders = array();

    /**
     * Initialize the response object and set the version value
     *
     * @param string $sVersion the HTTP version string, default: HTTP/1.1
     * @throws InvalidArgumentException
     */
    public function __construct($sVersion = self::VERSION_11)
    {
        if ($sVersion != self::VERSION_10 && $sVersion != self::VERSION_11) {
            throw new InvalidArgumentException('Invalid HTTP version given. The HTTP response version must be either HTTP/1.0 or HTTP/1.1.');
        }
        $this->sVersion = $sVersion;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->sVersion;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->aHeaders;
    }

    /**
     * Sets the content of the response
     *
     * @param ContentInterface|string $mContent
     *
     * @return Response
     */
    public function setContent($mContent)
    {
        $this->mContent = $mContent;
        if ($mContent instanceof ContentInterface) {
            $this->addHeaders($mContent->getResponseHeaders());
        }
        return $this;
    }

    /**
     * Sets the headers array, headers are appended to the current ones
     *
     * @param array $aHeaders
     *
     * @return Response
     */
    public function addHeaders(array $aHeaders)
    {
        foreach ($aHeaders as $sHeader) {
            $this->addHeader($sHeader);
        }
        return $this;
    }

    /**
     * Adds a single header to the end of the headers array
     *
     * @param string $sHeader
     *
     * @return Response
     */
    public function addHeader($sHeader)
    {
        $this->aHeaders[] = $sHeader;
        return $this;
    }

    /**
     * Clears the entire headers array
     *
     * @return Response
     */
    public function clearHeaders()
    {
        header_remove();
        $this->aHeaders = array();
        return $this;
    }

    /**
     * Sends all the headers to the client
     *
     * @return Response
     */
    public function send()
    {
        foreach ($this->aHeaders as $sHeader) {
            header($sHeader);
        }
        if ($this->mContent instanceof ContentInterface) {
            $this->mContent->render();
        } elseif (is_scalar($this->mContent)) {
            echo $this->mContent;
        }
        return $this;
    }

    /**
     * Sends a redirect and location header and dies
     *
     * @param string $sUrl
     * @param int $iRedirectHeader
     */
    public function redirect($sUrl, $iRedirectHeader = 302)
    {
        $this->clearHeaders();
        switch ($iRedirectHeader) {
            case 300:
                $this->addHeader($this->sVersion . ' ' . '301 Multiple Choices');
                break;
            case 301;
                $this->addHeader($this->sVersion . ' ' . '301 Moved Permanently');
                break;
            case 303;
                $this->addHeader($this->sVersion . ' ' . '303 See Other');
                break;
            case 307;
                $this->addHeader($this->sVersion . ' ' . '307 Temporary Redirect');
                break;
            default;
                $this->addHeader($this->sVersion . ' ' . '302 Found');
                break;
        }
        $this->addHeader('Location: ' . $sUrl);
        $this->send();
        die();
    }

    /**
     * @param RequestInterface $oRequest
     */
    public function redirectToReferrer(RequestInterface $oRequest)
    {
        $oServer = $oRequest->getServer();

        $url = (!empty($oServer->HTTPS)) ? "https://" . $oServer->SERVER_NAME . $oServer->REQUEST_URI : "http://" . $oServer->SERVER_NAME . $oServer->REQUEST_URI;
        if ($oServer->HTTP_REFERER == $url) {
            $this->redirect('/');
        }
        $this->redirect($oServer->HTTP_REFERER);
    }

    /**
     * @param Cookie $oCookie
     *
     * @return Response
     */
    public function setCookie(Cookie $oCookie)
    {
        $aCookieValues = array(
            'params' => ($oCookie->getValue() instanceof Params) ? $oCookie->getValue()->toArray() : $oCookie->getValue(),
            'expires' => ($oCookie->getExpires() instanceof \DateTime) ? $oCookie->getExpires()->getTimestamp() : 0
        );

        setcookie($oCookie->getName(), http_build_query($aCookieValues), $aCookieValues['expires']);
        return $this;
    }

    /**
     * @param Cookie $oCookie
     * @return Response
     */
    public function deleteCookie(Cookie $oCookie)
    {
        $oCookie->setValue('')
                ->setExpires((new \DateTime)->sub(new \DateInterval('P1D')));
        $this->setCookie($oCookie);
        return $this;
    }

}