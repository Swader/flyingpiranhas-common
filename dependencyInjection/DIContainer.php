<?php

namespace flyingpiranhas\common\dependencyInjection;

use flyingpiranhas\common\cache\interfaces\CacheInterface;
use flyingpiranhas\common\dependencyInjection\exceptions\DIException;
use ReflectionParameter;
use ReflectionClass;
use ReflectionMethod;
use flyingpiranhas\common\dependencyInjection\interfaces\DIContainerInterface;
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
 * @todo           fix recursion when class and name are the same
 *
 * @category       dependencyInjection
 * @package        flyingpiranhas.common
 * @license        BSD License
 * @version        0.01
 * @since          2012-11-23
 * @author         Ivan Pintar
 */
class DIContainer implements DIContainerInterface
{

    const SHARED_INSTANCE = 'shared';
    const NEW_INSTANCE = 'new';

    /**
     * A list of registered classes and instances.
     *
     * @var array
     */
    private $aRegistered = array();

    /**
     * @param string $sName
     *
     * @return bool
     */
    public function isRegistered($sName)
    {
        return isset($this->aRegistered[$sName]);
    }

    /**
     * Registers a class with the given name, type and additional constructor parameters
     *
     * @param string $sClass     the fully qualified class name
     * @param string $sName      the name under which the class should be registered
     * @param string $sType      the type of registration, if 'new', a new object will always be created, otherwise a first creation will register an instance of the class
     * @param array  $aOverrides parameters for the and dependency setters as key-value pairs where the key is the param name
     *
     * @return DIContainer
     */
    public function registerClass($sClass, $sName, $sType = self::SHARED_INSTANCE, array $aOverrides = array())
    {
        // register class
        $sName = '\\' . trim($sName, '\\');
        $this->aRegistered[$sName] = array(
            'class' => '\\' . trim($sClass, '\\'),
            'closure' => null,
            'instance' => null,
            'type' => $sType,
            'overrides' => $aOverrides
        );

        return $this;
    }

    /**
     * Registers an object with a given name.
     * Objects are always registered as shared instances.
     *
     * @param object $oInstance
     * @param string $sName
     *
     * @return DIContainer
     */
    public function registerInstance($oInstance, $sName = '')
    {
        if (!$sName) {
            $sName = get_class($oInstance);
        }
        $sName = '\\' . trim($sName, '\\');

        $this->aRegistered[$sName] = array(
            'class' => '\\' . trim(get_class($oInstance), '\\'),
            'closure' => null,
            'instance' => $oInstance,
            'type' => self::SHARED_INSTANCE,
            'overrides' => array()
        );

        return $this;
    }

    /**
     * Similar to register class, but registers a closure will be invoked when resolving
     *
     * @param Closure $oClosure
     * @param string  $sName
     * @param string  $sType
     * @param array   $aClosureParams
     *
     * @return DIContainer
     */
    public function registerClosure(Closure $oClosure,
                                    $sName,
                                    $sType = self::SHARED_INSTANCE,
                                    array $aClosureParams = array())
    {
        // register class
        $sName = '\\' . trim($sName, '\\');
        $this->aRegistered[$sName] = array(
            'class' => null,
            'closure' => $oClosure,
            'instance' => null,
            'type' => $sType,
            'overrides' => $aClosureParams
        );

        return $this;
    }

    /**
     * Resolves the dependencies for a registered class with a given name and returns its instances.
     * If the given $sName is a fully qualified class name, that class does not have to be registered.
     * If no $aDependencyNames are provided, they will be resolved with the names of the parameters in the class contstructor.
     * Dependencies defined as constructor params should be defined before any other parameters.
     * Optional parameters are NOT dependencies.
     *
     * @param string $sName      a fully qualified class name or a name of one of the registered classes or instances
     * @param array  $aOverrides parameters as key-value pairs where the key is the param name
     *
     * @return object
     */
    public function resolve($sName, array $aOverrides = array())
    {
        $sName = '\\' . trim($sName, '\\');

        // if resolving a dependency to the container, return self;
        if ($sName == '\\' . trim(get_class($this), '\\')) {
            return $this;
        }

        if (isset($this->aRegistered[$sName])) {
            $aParams = array_merge($this->aRegistered[$sName]['overrides'], $aOverrides);

            // if an instance with the given name is registered, return that
            if ($this->aRegistered[$sName]['instance']) {
                return $this->aRegistered[$sName]['instance'];
            }

            // if a closure with this name exists
            if ($this->aRegistered[$sName]['closure']) {
                $oClosure = $this->aRegistered[$sName]['closure'];
                if ($this->aRegistered[$sName]['type'] == self::SHARED_INSTANCE) {
                    $this->aRegistered[$sName]['instance'] = $oClosure($aParams);
                    $this->aRegistered[$sName]['class'] = get_class($this->aRegistered[$sName]['instance']);
                    return $this->aRegistered[$sName]['instance'];
                } else {
                    return $oClosure($aParams);
                }

            }

            if ($this->aRegistered[$sName]['type'] == self::SHARED_INSTANCE) {
                // it the name is registered as 'shared', create an instance of the class and resolve that
                $this->aRegistered[$sName]['instance'] =
                    $this->resolve($this->aRegistered[$sName]['class'], $aParams);

                // return the instance
                return $this->aRegistered[$sName]['instance'];
            } else {
                // if the class is registered as 'new', resolve a new instance and return it
                return $this->resolve($this->aRegistered[$sName]['class'], $aParams);
            }
        } else {
            // if the class was not registered:
            // resolve constructor
            $oResult = $this->resolveConstructor($sName, $aOverrides);

            // resolve setters
            $this->resolveSetters($oResult, $aOverrides);

            // return the instance
            return $oResult;
        }
    }

    /**
     * @param string $sName
     * @param array  $aOverrides
     *
     * @return object
     */
    private function resolveConstructor($sName, array &$aOverrides)
    {
        $oReflector = new ReflectionClass($sName);
        $oConstructor = $oReflector->getConstructor();

        if ($oConstructor && count($oConstructor->getParameters())) {
            $aCtorParams = array();

            foreach ($oConstructor->getParameters() as $oParam) {
                $aCtorParams[] = $this->resolveParam($oParam, $aOverrides);
            }

            // create a new instance with the given params
            return $oReflector->newInstanceArgs($aCtorParams);
        }

        // if there are no constructor params, create a new instance of the class
        return new $sName;
    }

    /**
     * @param object $oObject
     * @param array  $aOverrides
     */
    private function resolveSetters($oObject, array &$aOverrides)
    {
        $oReflector = new ReflectionClass($oObject);

        // loop through methods
        foreach ($oReflector->getMethods(ReflectionMethod::IS_PUBLIC) as $oMethod) {
            if (strpos($oMethod->getDocComment(), '@dependency')) {
                $aSetterParams = array();

                foreach ($oMethod->getParameters() as $oParam) {
                    $aSetterParams[] = $this->resolveParam($oParam, $aOverrides);
                }
                $oMethod->invokeArgs($oObject, $aSetterParams);
            }
        }
    }

    /**
     * @param ReflectionParameter $oParam
     * @param array               $aOverrides
     *
     * @return mixed
     * @throws DIException
     */
    private function resolveParam(ReflectionParameter $oParam, array &$aOverrides)
    {
        if (!$oParam->isOptional() && $oParam->getClass()) {
            // if param is overriden
            if (isset($aOverrides[$oParam->name])) {
                $mOverridenParam = $aOverrides[$oParam->name];

                // if param is an object of the same class return the object
                if (is_object($mOverridenParam) && is_subclass_of($mOverridenParam, $oParam->getClass()->name)) {
                    return $mOverridenParam;
                }

                // if param is a string try to resolve it using the string as a name
                if (is_string($mOverridenParam)) {
                    return $this->resolve($mOverridenParam);
                }
            }

            // if param is not overriden
            return $this->resolve($oParam->getClass()->name);
        }

        // return the param from the overrides
        if (isset($aOverrides[$oParam->name])) {
            return $aOverrides[$oParam->name];
        }

        if ($oParam->isOptional()) {
            return $oParam->getDefaultValue();
        }

        throw new DIException('Could not resolve parameter: ' . $oParam->name);
    }

}