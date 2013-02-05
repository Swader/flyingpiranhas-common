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

        $oRegistration = new DIRegistration();
        $oRegistration
            ->setClass('\\' . trim($sClass, '\\'))
            ->setType($sType)
            ->setOverrides($aOverrides);

        $this->aRegistered[$sName] = $oRegistration;

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

        $oRegistration = new DIRegistration();
        $oRegistration
            ->setInstance($oInstance)
            ->setType(self::SHARED_INSTANCE);

        $this->aRegistered[$sName] = $oRegistration;

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

        $oRegistration = new DIRegistration();
        $oRegistration
            ->setClosure($oClosure)
            ->setType($sType)
            ->setOverrides($aClosureParams);

        $this->aRegistered[$sName] = $oRegistration;

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
            /** @var $oRegistration DIRegistration */
            $oRegistration = $this->aRegistered[$sName];

            $aParams = array_merge($oRegistration->getOverrides(), $aOverrides);

            // if the name and class are the same
            if ($sName == $oRegistration->getClass()) {
                // resolve constructor
                $oInstance = $this->resolveConstructor($sName, $aParams);

                // resolve setters
                $this->resolveSetters($oInstance, $aParams);

                // if registered as a shared instance
                if ($oRegistration->getType() == self::SHARED_INSTANCE) {
                    $oRegistration->setInstance($oInstance);
                }

                // return the instance
                return $oInstance;
            }


            // if an instance with the given name is registered, return that
            if ($oRegistration->getInstance()) {
                return $oRegistration->getInstance();
            }

            // if a closure with this name exists
            if ($oRegistration->getClosure()) {
                $oClosure = $oRegistration->getClosure();
                $oInstance = $oClosure($aParams);

                if ($oRegistration->getType() == self::SHARED_INSTANCE) {
                    $oRegistration->setInstance($oInstance);
                }

                return $oInstance;
            }

            // resolve a new instance and return it
            $oInstance = $this->resolve($oRegistration->getClass(), $aParams);

            // it the name is registered as 'shared', save the instance
            if ($oRegistration->getType() == self::SHARED_INSTANCE) {
                $oRegistration->setInstance($oInstance);
            }

            return $oInstance;
        } else {
            // if the class was not registered

            // resolve constructor
            $oInstance = $this->resolveConstructor($sName, $aOverrides);

            // resolve setters
            $this->resolveSetters($oInstance, $aOverrides);

            // return the instance
            return $oInstance;
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

            // create a new instance with the given params
            return $oReflector->newInstanceArgs(
                $this->resolveMethod($oConstructor, $aOverrides)
            );
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
                $oMethod->invokeArgs(
                    $oObject,
                    $this->resolveMethod($oMethod, $aOverrides)
                );
            }
        }
    }

    /**
     * @param ReflectionMethod $oMethod
     * @param array            $aOverrides
     * @param array            $aCommentOverrides
     *
     * @return array
     */
    private function resolveMethod(ReflectionMethod $oMethod, array &$aOverrides)
    {
        $aMatches = array();
        preg_match('/@dependency\((.*)\)/', $oMethod->getDocComment(), $aMatches);

        $aCommentOverrides = array();
        if ($aMatches) {
            $aCommentOverrides = json_decode($aMatches[1], true);
        }

        $aSetterParams = array();
        foreach ($oMethod->getParameters() as $oParam) {
            $aSetterParams[] = $this->resolveParam($oParam, $aOverrides, $aCommentOverrides);
        }

        return $aSetterParams;
    }

    /**
     * @param ReflectionParameter $oParam
     * @param array               $aOverrides
     * @param array               $aCommentOverrides
     *
     * @return mixed
     * @throws DIException
     */
    private function resolveParam(ReflectionParameter $oParam, array &$aOverrides, array &$aCommentOverrides)
    {
        if (!$oParam->isOptional() && $oParam->getClass()) {
            // if param is overriden
            if (isset($aOverrides[$oParam->name]) || isset($aCommentOverrides[$oParam->name])) {
                $mOverridenParam =
                    (isset($aCommentOverrides[$oParam->name]))
                        ? $aCommentOverrides[$oParam->name]
                        : $aOverrides[$oParam->name];

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