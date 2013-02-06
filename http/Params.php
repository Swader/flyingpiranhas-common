<?php

namespace flyingpiranhas\common\http;

use IteratorAggregate;
use ArrayIterator;
use ArrayAccess;

/**
 * The Params object is a simple iterable object that can also be used as an array.
 * The main use is wrapping the $_GET, $_POST and $_FILES superglobals into an easy to use api,
 * but any array can be turned into a Params object.
 *
 * @category       http
 * @package        flyingpiranhas.http
 * @license        Apache-2.0
 * @version        0.01
 * @since          2012-09-07
 * @author         Ivan Pintar
 */
class Params implements IteratorAggregate, ArrayAccess
{

    /** @var string */
    protected $sName = '';

    /** @var array */
    protected $aParams = array();

    /**
     * Parses the given params array and builds a tree of Params objects
     *
     * @param array  $aParams
     * @param string $sName
     */
    public function __construct(array $aParams = array(), $sName = '')
    {
        $this->sName = $sName;
        foreach ($aParams as $sName => $mParam) {
            $this->$sName = $mParam;
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->sName;
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->aParams);
    }

    /**
     * @param string $sKey
     *
     * @return bool
     */
    public function offsetExists($sKey)
    {
        return isset($this->$sKey);
    }

    /**
     * @param string $sKey
     *
     * @return Params|mixed|null|string
     */
    public function offsetGet($sKey)
    {
        return $this->$sKey;
    }

    /**
     * @param string $sKey
     * @param mixed  $mVal
     */
    public function offsetSet($sKey, $mVal)
    {
        $this->$sKey = $mVal;
    }

    /**
     * @param string $sKey
     */
    public function offsetUnset($sKey)
    {
        unset($this->$sKey);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $aParams = $this->aParams;
        foreach ($aParams as &$mValue) {
            if ($mValue instanceof Params) {
                $mValue = $mValue->toArray();
            }
        }
        return $aParams;
    }

    /**
     * @param string $sName
     *
     * @return string|Params|null
     */
    public function __get($sName)
    {
        return isset($this->aParams[$sName]) ? $this->aParams[$sName] : null;
    }

    /**
     * @param string              $sName
     *
     * @param string|array|Params $mValue
     */
    public function __set($sName, $mValue)
    {
        $this->aParams[$sName] = $this->buildValue($sName, $mValue);
    }

    /**
     * @param string $sName
     *
     * @return bool
     */
    public function __isset($sName)
    {
        return isset($this->aParams[$sName]);
    }

    /**
     * @param string       $sName
     * @param string|array $mValue
     *
     * @return string|Params
     */
    protected function buildValue($sName, $mValue)
    {
        if ($mValue instanceof Params) {
            return $mValue;
        }

        return (is_array($mValue)) ? new Params($mValue, $sName) : $mValue;
    }

}