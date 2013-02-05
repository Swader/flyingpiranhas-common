<?php

namespace flyingpiranhas\common\dependencyInjection\interfaces;

use Closure;

/**
 * @category       dependencyInjection
 * @package        flyingpiranhas.common
 * @license        BSD License
 * @version        0.01
 * @since          2012-11-23
 * @author         Ivan Pintar
 */
interface DIContainerInterface
{

    /**
     * @param string $sName
     *
     * @return bool
     */
    public function isRegistered($sName);

    /**
     * Registers a class with the given name, type and additional constructor parameters
     *
     * @param string $sClass     the fully qualified class name
     * @param string $sName      the name under which the class should be registered
     * @param string $sType      the type of registration, if 'new', a new object will always be created, otherwise a first creation will register an instance of the class
     * @param array  $aOverrides parameters as key-value pairs where the key is the param name
     *
     * @return DIContainerInterface
     */
    public function registerClass($sClass,
                                  $sName,
                                  $sType = 'new',
                                  array $aOverrides = array());

    /**
     * Registers an object with a given name.
     * Objects are always registered as shared instances.
     *
     * @param object $oInstance
     * @param string $sName
     *
     * @return DIContainerInterface
     */
    public function registerInstance($oInstance, $sName = '');

    /**
     * Similar to register class, but registers a closure that will be invoked
     *
     * @param Closure $oClosure
     * @param string  $sName
     * @param string  $sType
     * @param array   $aClosureParams
     *
     * @return DIContainerInterface
     */
    public function registerClosure(Closure $oClosure,
                                    $sName,
                                    $sType = 'new',
                                    array $aClosureParams = array());

    /**
     * Resolves the dependencies for a registered class with a given name and returns its instances.
     * If the given $sName is a fully qualified class name, that class does not have to be registered.
     * If no $aDependencyNames are provided, they will be resolved with the names of the parameters in the class contstructor.
     * Optional parameters are NOT dependencies.
     *
     * @param string $sName      a fully qualified class name or a name of one of the registered classes or instances
     * @param array  $aOverrides parameters as key-value pairs where the key is the param name
     *
     * @return object
     */
    public function resolve($sName, array $aOverrides = array());

}