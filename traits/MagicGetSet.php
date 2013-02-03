<?php

namespace flyingpiranhas\common\traits;

use InvalidArgumentException;

/**
 * @category       traits
 * @package        flyingpiranhas.common
 * @license        BSD License
 * @version        0.01
 * @since          2012-11-23
 * @author         Bruno Škvorc <bruno@skvorc.me>
 */
trait MagicGetSet
{

    /**
     * Magic setter for changing property values
     *
     * @param string $sName
     * @param mixed  $mValue
     *
     * @return MagicGetSet
     * @throws InvalidArgumentException
     */
    public function __set($sName, $mValue)
    {
        if (!is_scalar($sName)) {
            throw new InvalidArgumentException('Name must be scalar value');
        }

        $sField = strtolower($sName);
        $sSetter = 'set' . ucfirst($sField);

        if (!property_exists($this, $sField)) {
            throw new InvalidArgumentException(
                    'No such property (' . $sField . ') available for this class.'
            );
        }

        if (method_exists($this, $sSetter) && is_callable(array($this, $sSetter))) {
            $this->$sSetter($mValue);
        } else {
            $this->$sField = $mValue;
        }

        return $this;
    }

    /**
     * Magic getter for fetching properties
     *
     * @param string $sName
     *
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function __get($sName)
    {

        if (!is_scalar($sName)) {
            throw new InvalidArgumentException('Name must be scalar value');
        }

        $sField = strtolower($sName);
        $sGetter = 'get' . ucfirst($sField);

        if (!property_exists($this, $sField)) {
            throw new InvalidArgumentException(
                    'No such property (' . $sField . ') available for this class.'
            );
        }

        return (method_exists($this, $sGetter) && is_callable(array($this, $sGetter))) ? $this->$sGetter() : $this->$sField;
    }

}
