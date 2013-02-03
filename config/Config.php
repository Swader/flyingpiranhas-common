<?php

namespace flyingpiranhas\common\config;

use IteratorAggregate;
use ArrayIterator;
use ArrayAccess;

/**
 * The Config object holds a list of parameters that can be either scalar values,
 * or other Config objects with their own properties.
 * It can be used as an object or array.
 *
 * @category       config
 * @package        flyingpiranhas.common
 * @license        BSD License
 * @version        0.01
 * @since          2012-11-23
 * @author         Ivan Pintar
 */
class Config implements IteratorAggregate, ArrayAccess
{

    /** @var array */
    private $aProperties = array();

    /**
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->aProperties);
    }

    /**
     * @param string|int $sKey
     *
     * @return bool
     */
    public function offsetExists($sKey)
    {
        return isset($this->$sKey);
    }

    /**
     * @param string|int $sKey
     *
     * @return string|Config
     */
    public function offsetGet($sKey)
    {
        return $this->$sKey;
    }

    /**
     * @param string|int          $sKey
     * @param string|array|Config $mVal
     */
    public function offsetSet($sKey, $mVal)
    {
        $this->$sKey = $mVal;
    }

    /**
     * @param string|int $sKey
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
        $aToReturn = array();
        foreach ($this->aProperties as $sName => $mProperty) {
            if ($mProperty instanceof Config) {
                $aToReturn[$sName] = $mProperty->toArray();
            } else {
                $aToReturn[$sName] = $mProperty;
            }
        }
        return $aToReturn;
    }

    /**
     * @param string|int $sName
     *
     * @return string|Config
     */
    public function __get($sName)
    {
        if (!isset($this->aProperties[$sName])) {
            $this->aProperties[$sName] = new Config;
        }

        return $this->aProperties[$sName];
    }

    /**
     * @param string|int          $sName
     * @param string|array|Config $mValue
     */
    public function __set($sName, $mValue)
    {
        $this->aProperties[$sName] = $mValue;
    }

    /**
     * @param string|int $sName
     *
     * @return bool
     */
    public function __isset($sName)
    {
        return isset($this->aProperties[$sName]);
    }

    public function __clone()
    {
        foreach ($this->aProperties as $sName => $mValue) {
            if ($mValue instanceof Config) {
                $this->aProperties[$sName] = clone $mValue;
            } else {
                $this->aProperties[$sName] = $mValue;
            }
        }
    }

}