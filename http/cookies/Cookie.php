<?php

namespace flyingpiranhas\common\http\cookies;

use DateTime;

/**
 * The Cookie object is a simple iterable object that can also be used as an array.
 * The main use is wrapping the elements of the $_COOKIE superglobal with an easy to use api.
 *
 * @category       http
 * @package        flyingpiranhas.http
 * @license        Apache-2.0
 * @version        0.01
 * @since          2012-09-07
 * @author         Ivan Pintar
 */
class Cookie
{

    const VALUES_KEY = 'fpCookieValues';
    const EXPIRY_KEY = 'fpCookieExpiry';

    /** @var string */
    protected $sName;

    /** @var DateTime */
    protected $dExpires;

    /** @var array|string */
    protected $aValue;

    /**
     * @param string       $sName
     * @param array|string $mParams
     * @param DateTime     $dExpires
     */
    public function __construct($sName, $mParams, DateTime $dExpires = null)
    {
        if ($dExpires) {
            $this->dExpires = $dExpires;
        }

        $this->sName = $sName;
        $this->aValue = $mParams;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->sName;
    }

    /**
     * @return DateTime
     */
    public function getExpires()
    {
        return $this->dExpires;
    }

    /**
     * @return array|string
     */
    public function getValue()
    {
        return $this->aValue;
    }

    /**
     * @param DateTime $dExpires
     *
     * @return Cookie
     */
    public function setExpires(\DateTime $dExpires)
    {
        $this->dExpires = $dExpires;
        return $this;
    }

    /**
     * @param array|string $mValue
     *
     * @return Cookie
     */
    public function setValue($mValue)
    {
        $this->aValue = $mValue;
        return $this;
    }
}