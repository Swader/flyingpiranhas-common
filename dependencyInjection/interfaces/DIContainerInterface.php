<?php

namespace flyingpiranhas\common\dependencyInjection\interfaces;

use Closure;

/**
 * A simple dependency injection container used by other flyingpiranhas components
 * to resolve dependencies.
 * It uses reflection to detect non optional constructor parameters when resolving
 * instances of classes.
 * Classes can be registered as 'shared' or 'new'. All class that requires a shared instance
 * of some class will get a reference to the same object. That object is effectively a singleton.
 * If some class is registered as 'new', a new object will be provided when requested.
 *
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
     * @param string $sClass the fully qualified class name
     * @param string $sName the name under which the class should be registered
     * @param string $sType the type of registration, if 'new', a new object will always be created, otherwise a first creation will register an instance of the class
     * @param array $aDependencyNames dependency names
     * @param array $aAddCtorParams additional parameters for the constructor of the class, in the order in which they appear, including dependencies
     *
     * @return DIContainerInterface
     */
    public function registerClass($sClass,
                                  $sName,
                                  $sType = 'new',
                                  array $aDependencyNames = array(),
                                  array $aAddCtorParams = array());

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
     * Similar to register class, but registers a closure that can be invoked
     *
     * @param Closure $oClosure
     * @param string $sName
     * @param string $sType
     * @param array $aClosureParams
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
     * Dependencies defined as constructor params should be defined before any other parameters.
     * Optional parameters are NOT dependencies.
     *
     * @param string $sName a fully qualified class name or a name of one of the registered classes or instances
     * @param array $aDependencyNames a list of dependency names as registered with the container, in the order in which they appear
     * @param array $aAddCtorParams additional parameters to pass to the constructor when creating the object
     *
     * @return object
     */
    public function resolve($sName, array $aDependencyNames = array(), array $aAddCtorParams = array());

}