<?php

namespace flyingpiranhas\common\dependencyInjection;

use flyingpiranhas\common\cache\interfaces\CacheInterface;
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
 * @todo   fix recursion when class and name are the same
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
    const NEW_INSTANCE    = 'new';

    /** @var string */
    private $sCacheKey = 'fpDICache';

    /** @var CacheInterface */
    private $oCache;

    /**
     * A list of registered classes and instances.
     *
     * @var array
     */
    public $aRegistered = array();

    /**
     * A list of dependencies to be loaded from cache.
     * As dependencies for a class are discovered they are saved here
     * and this array is saved to cache on destruction of the DIcontainer.
     *
     * @var array
     */
    private $aDependencies = array();

    /**
     * @param CacheInterface $oCache
     */
    public function __construct(CacheInterface $oCache)
    {
        $this->oCache = $oCache;
        if ($this->oCache->exists($this->sCacheKey)) {
            $this->aDependencies = $this->oCache->get($this->sCacheKey);
        }
    }

    public function __destruct()
    {
        if (!$this->oCache->exists($this->sCacheKey)) {
            $this->oCache->set($this->sCacheKey, $this->aDependencies);
        }
    }

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
     * @param string $sClass           the fully qualified class name
     * @param string $sName            the name under which the class should be registered
     * @param string $sType            the type of registration, if 'new', a new object will always be created, otherwise a first creation will register an instance of the class
     * @param array  $aDependencyNames dependency names
     * @param array  $aAddCtorParams   additional parameters for the constructor of the class, in the order in which they appear, including dependencies
     *
     * @return DIContainer
     */
    public function registerClass($sClass, $sName, $sType = self::SHARED_INSTANCE, array $aDependencyNames = array(), array $aAddCtorParams = array())
    {
        // register class
        $sName                     = trim($sName, '\\');
        $this->aRegistered[$sName] = array(
            'class'           => $sClass,
            'closure'         => null,
            'instance'        => null,
            'type'            => $sType,
            'dependencyNames' => $aDependencyNames,
            'addCtorParams'   => $aAddCtorParams
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
        $sName = trim($sName, '\\');

        $this->aRegistered[$sName] = array(
            'class'           => get_class($oInstance),
            'closure'         => null,
            'instance'        => $oInstance,
            'type'            => self::SHARED_INSTANCE,
            'dependencyNames' => array(),
            'addCtorParams'   => array()
        );

        return $this;
    }

    /**
     * Similar to register class, but registers a closure that can be invoked
     *
     * @param Closure $oClosure
     * @param string  $sName
     * @param string  $sType
     * @param array   $aClosureParams
     *
     * @return DIContainer
     */
    public function registerClosure(Closure $oClosure, $sName, $sType = self::SHARED_INSTANCE, array $aClosureParams = array())
    {
        // register class
        $sName                     = trim($sName, '\\');
        $this->aRegistered[$sName] = array(
            'class'           => null,
            'closure'         => $oClosure,
            'instance'        => null,
            'type'            => $sType,
            'dependencyNames' => array(),
            'addCtorParams'   => $aClosureParams
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
     * @param string $sName            a fully qualified class name or a name of one of the registered classes or instances
     * @param array  $aDependencyNames a list of dependency names as registered with the container, in the order in which they appear
     * @param array  $aAddCtorParams   additional parameters to pass to the constructor when creating the object
     *
     * @return object
     *
     * @todo allow for dependencies and ctor params to take in null (array overrides setup, null does nothing)
     */
    public function resolve($sName, array $aDependencyNames = array(), array $aAddCtorParams = array())
    {

        $sName = trim($sName, '\\');

        // if resolving a dependency to the container, return self;
        if ($sName == trim(get_class($this), '\\')) {
            return $this;
        }

        if (isset($this->aRegistered[$sName])) {
            $aAddCtorParams   = ($aAddCtorParams) ? $aAddCtorParams : $this->aRegistered[$sName]['addCtorParams'];
            $aDependencyNames = ($aDependencyNames) ? $aDependencyNames : $this->aRegistered[$sName]['dependencyNames'];

            // if an instance with the given name is registered, return that
            if ($this->aRegistered[$sName]['instance']) {
                return $this->aRegistered[$sName]['instance'];
            }

            // if a closure with this name exists
            if ($this->aRegistered[$sName]['closure']) {
                $oClosure = $this->aRegistered[$sName]['closure'];
                if ($this->aRegistered[$sName]['type'] == self::SHARED_INSTANCE) {
                    $this->aRegistered[$sName]['instance'] = $oClosure($aAddCtorParams);
                    $this->aRegistered[$sName]['class']    = get_class($this->aRegistered[$sName]['instance']);
                    return $this->aRegistered[$sName]['instance'];
                } else {
                    return $oClosure($aAddCtorParams);
                }

            }

            if ($this->aRegistered[$sName]['type'] == self::SHARED_INSTANCE) {
                // it the name is registered as 'shared', create an instance of the class and resolve that
                $this->aRegistered[$sName]['instance'] =
                    $this->resolve($this->aRegistered[$sName]['class'], $aDependencyNames, $aAddCtorParams);

                // return the instance
                return $this->aRegistered[$sName]['instance'];
            } else {
                // if the class is registered as 'new', resolve a new instance and return it
                return $this->resolve($this->aRegistered[$sName]['class'], $aDependencyNames, $aAddCtorParams);
            }
        } else {
            // if the class was not registered:
            // resolve constructor
            $oResult = $this->resolveConstructor($sName, $aDependencyNames, $aAddCtorParams);

            // resolve setters
            $this->resolveSetters($oResult, $aDependencyNames);

            // return the instance
            return $oResult;
        }
    }

    /**
     * @param string $sName
     * @param array  $aDependencyNames
     * @param array  $aAddCtorParams
     *
     * @return object
     */
    private function resolveConstructor($sName, array &$aDependencyNames, array &$aAddCtorParams)
    {
        $oReflector   = new \ReflectionClass($sName);
        $oConstructor = $oReflector->getConstructor();

        $oResult = null;
        if (isset($this->aDependencies[$sName]['ctor'])) {
            // array to build constructor params
            $aCtorParams = array();

            foreach ($this->aDependencies[$sName]['ctor'] as $sParamName) {
                $aCtorParams[] = (isset($aAddCtorParams[$sParamName])) ? $aAddCtorParams[$sParamName] : $this->resolve($sParamName);
            }
            $oResult = $oReflector->newInstanceArgs($aCtorParams);
        } else if ($oConstructor && count($oConstructor->getParameters())) {
            // array to build constructor params
            $aCtorParams = array();

            foreach ($oConstructor->getParameters() as $oParam) {
                // if the param is not optional and has a class type, resolve it and save to the $aConstructorParams array
                if (isset($aAddCtorParams[$oParam->name])) {
                    // add parameteres from the $aAddConstructorParams
                    $aCtorParams[]                         = $aAddCtorParams[$oParam->name];
                    $this->aDependencies[$sName]['ctor'][] = $oParam->name;
                } else if (!$oParam->isOptional() && $oParam->getClass()) {
                    if (isset($aDependencyNames[$oParam->name])) {
                        $this->aDependencies[$sName]['ctor'][] = $aDependencyNames[$oParam->name];
                        $aCtorParams[]                         = $this->resolve($aDependencyNames[$oParam->name]);
                    } else {
                        $this->aDependencies[$sName]['ctor'][] = $oParam->getClass()->name;
                        $aCtorParams[]                         = $this->resolve($oParam->getClass()->name);
                    }
                }
            }


            // create a new instance with the given params
            $oResult = $oReflector->newInstanceArgs($aCtorParams);
        } else {

            // it there are no constructor params, create a new instance of the class
            $oResult = new $sName;
        }

        return $oResult;
    }

    /**
     * @param object $oObject
     * @param array  $aDependencyNames
     */
    private function resolveSetters($oObject, array &$aDependencyNames)
    {
        $sObjectName = trim(get_class($oObject), '\\');

        if (isset($this->aDependencies[$sObjectName]['setters'])) {
            foreach ($this->aDependencies[$sObjectName]['setters'] as $sSetter => $aParamNames) {
                $aSetterParams = array();
                foreach ($aParamNames as $sParamName) {
                    $aSetterParams[] =
                        (isset($aDependencyNames[$sParamName]))
                            ? $this->resolve($aDependencyNames[$sParamName])
                            : $this->resolve($sParamName);
                }

                call_user_func_array(array($oObject, $sSetter), $aSetterParams);
            }
        } else {
            $oReflector = new \ReflectionClass($oObject);

            // loop through methods
            foreach ($oReflector->getMethods(\ReflectionMethod::IS_PUBLIC) as $oMethod) {
                if (strpos($oMethod->getDocComment(), '@dependency')) {
                    $aSetterParams = array();

                    foreach ($oMethod->getParameters() as $oParam) {
                        if (isset($aDependencyNames[$oParam->name])) {
                            $this->aDependencies[$sObjectName]['setters'][$oMethod->name][] = $aDependencyNames[$oParam->name];
                            $aSetterParams[]                                                = $this->resolve($aDependencyNames[$oParam->name]);
                        } else {
                            $this->aDependencies[$sObjectName]['setters'][$oMethod->name][] = $oParam->getClass()->name;
                            $aSetterParams[]                                                = $this->resolve($oParam->getClass()->name);
                        }
                    }
                    $oMethod->invokeArgs($oObject, $aSetterParams);
                }
            }
        }
    }

}