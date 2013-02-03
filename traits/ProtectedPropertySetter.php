<?php

namespace flyingpiranhas\common\traits;

/**
 * The ProtectedPropertySetter provides a protected setProperties method
 * which can be used to set a number of properties by passing in an array of key => value pairs.
 * For each key, there should be a setKey() method which will be used to set the value.
 * If no such method is present, the key is ignored.
 *
 * @author         Ivan Pintar
 * @author         Bruno Škvorc <bruno@skvorc.me>
 * @edit 2012-10-22
 */
trait ProtectedPropertySetter
{

    /**
     * @param array $aProperties
     *
     * @return ProtectedPropertySetter
     */
    protected function setProperties(array $aProperties)
    {
        foreach ($aProperties as $sSetter => $mValue) {
            $sSetter = 'set' . ucfirst($sSetter);
            if (method_exists($this, $sSetter)) {
                $this->$sSetter($mValue);
            }
        }
        return $this;
    }

}
