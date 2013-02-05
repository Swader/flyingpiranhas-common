<?php

namespace flyingpiranhas\common\dependencyInjection;

use Closure;

/**
 * @category       dependencyInjection
 * @package        flyingpiranhas.common
 * @license        BSD License
 * @version        0.01
 * @since          2012-11-23
 * @author         Ivan Pintar
 */
class DIRegistration
{

    /** @var string */
    private $sClass = '';

    /** @var Closure|null */
    private $oClosure = null;

    /** @var object|null */
    private $oInstance = null;

    /** @var string */
    private $sType = DIContainer::NEW_INSTANCE;

    /** @var array */
    private $aOverrides = array();

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->sClass;
    }

    /**
     * @return object|null
     */
    public function getInstance()
    {
        return $this->oInstance;
    }

    /**
     * @return Closure|null
     */
    public function getClosure()
    {
        return $this->oClosure;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->sType;
    }

    /**
     * @return array
     */
    public function getOverrides()
    {
        return $this->aOverrides;
    }

    /**
     * @param array $aOverrides
     *
     * @return DIRegistration
     */
    public function setOverrides(array $aOverrides)
    {
        $this->aOverrides = $aOverrides;
        return $this;
    }

    /**
     * @param Closure|null $oClosure
     *
     * @return DIRegistration
     */
    public function setClosure(Closure $oClosure)
    {
        $this->oClosure = $oClosure;
        return $this;
    }

    /**
     * @param object|null $oInstance
     *
     * @return DIRegistration
     */
    public function setInstance($oInstance)
    {
        $this->oInstance = $oInstance;
        $this->setClass(get_class($oInstance));
        return $this;
    }

    /**
     * @param string $sType
     *
     * @return DIRegistration
     */
    public function setType($sType)
    {
        $this->sType = $sType;
        return $this;
    }

    /**
     * @param string $sClass
     *
     * @return DIRegistration
     */
    public function setClass($sClass)
    {
        $this->sClass = '\\' . trim($sClass, '\\');
        return $this;
    }

}
