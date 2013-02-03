<?php

namespace flyingpiranhas\common\http\cookies;

use DateTime;
use flyingpiranhas\common\http\Params;

/**
 * The Cookie object is a simple iterable object that can also be used as an array.
 * The main use is wrapping the elements of the $_COOKIE superglobal with an easy to use api.
 *
 * @category       http
 * @package        flyingpiranhas.http
 * @license        BSD License
 * @version        0.01
 * @since          2012-09-07
 * @author         Ivan Pintar
 */
class Cookie
{

    /** @var string */
    protected $sName;

    /** @var DateTime */
    protected $dExpires;

    /** @var Params|string */
    protected $oValue;

    /**
     * @param string       $sName
     * @param Params|array $aParams
     * @param DateTime     $dExpires
     */
    public function __construct($sName, $aParams, DateTime $dExpires = null)
    {
        if ($dExpires) {
            $this->dExpires = $dExpires;
        }

        $this->sName  = $sName;
        $this->oValue = $this->buildValue($sName, $aParams);
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
     * @return Params|string
     */
    public function getValue()
    {
        return $this->oValue;
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
     * @param Params|array|string $mValue
     *
     * @return Cookie
     */
    public function setValue($mValue)
    {
        $this->oValue = $this->buildValue('', $mValue);
        return $this;
    }

    /**
     * @param string              $sName
     * @param Params|array|string $mValue
     *
     * @return Params|string
     */
    protected function buildValue($sName, $mValue)
    {
        return (is_array($mValue)) ? new Params($mValue, $sName) : $mValue;
    }

}