<?php

namespace flyingpiranhas\common\database;

use flyingpiranhas\common\database\interfaces\AdapterInterface;
use flyingpiranhas\common\exceptions\FpException;

/**
 * The Database registry will hold settings for various adapters
 * and will fetch their instantiated instances when needed.
 *
 * Adapters will not be instantiated before they are needed, and once
 * created the same instance will always be used
 *
 * @category       database
 * @package        flyingpiranhas.common
 * @license        Apache-2.0
 * @version        0.01
 * @since          2012-11-24
 * @author         Bruno Škvorc <bruno@skvorc.me>
 */
class Registry
{

    /** @var array */
    protected static $aAdapters = array();

    /** @var string */
    protected static $sSupportedFlags = 'mysql, pgsql';

    /** @var bool */
    protected static $bAutoAdd = true;

    /**
     * Bulk method for self::registerAdapter
     * Each array element must have a key => value combo in which key
     * is the unique adapter name, and value is an array of settings or
     * the path to a configuration file
     *
     * @see self::registerAdapter
     *
     * @param array $aArray
     */
    public static function registerAdapters(array $aArray)
    {
        if (!empty($aArray)) {
            foreach ($aArray as $sName => &$mValue) {
                if (is_numeric($sName) && $mValue instanceof AdapterInterface) {
                    $sName = $mValue->getAdapterName();
                }
                self::registerAdapter($sName, $mValue);
            }
        }
    }

    /**
     * Registers an adapter for later use. Will not instantiate until
     * needed, and from that moment on will return the same instance.
     *
     * If config file is provided as input, the configuration is instantly
     * parsed and saved in array form.
     *
     * If a config file is provided as mInput and it contains an adapterName flag,
     * the name passed through sName is ignored and overwritten by the name
     * specified in the config file. To custom name your config file adapters,
     * exclude the adapterName flag.
     *
     * @param string                         $sName
     * @param AdapterInterface|array|string  $mInput
     *
     * @throws DatabaseRegistryException
     */
    public static function registerAdapter($sName, $mInput)
    {
        if (!isset(self::$aAdapters[$sName])) {
            if (is_string($mInput)) {
                if (is_readable($mInput) && strpos($mInput, '.ini')) {
                    $mInput = parse_ini_file($mInput);
                } else {
                    throw new DatabaseRegistryException($mInput . ' is not a valid configuration ini file.');
                }
            }

            if (
                    is_array($mInput) && (!isset($mInput['dbType'])
                    || !in_array(strtolower($mInput['dbType']), array('mysql', 'pgsql')))
            ) {
                throw new DatabaseRegistryException(
                        'The passed configuration array is missing a dbType flag.
                        Currently supported dbType flags are ' . self::$sSupportedFlags . '.');
            }

            if (is_array($mInput) && isset($mInput['adapterName'])) {
                $sName = $mInput['adapterName'];
            }

            self::$aAdapters[$sName] = $mInput;
        }
    }

    /**
     * Sets the autoAdd flag to true or false.
     * The default value is TRUE and is applied application-wide
     *
     * When true, all database adapters will add themselves to the
     * Registry automatically when opening connections and will stay
     * there for later fetching. This saves resources and prevents
     * opening many connection. It's effectively a better mass-singleton.
     *
     * @param bool $bAutoAdd
     */
    public static function setAutoAdd($bAutoAdd)
    {
        self::$bAutoAdd = (bool) $bAutoAdd;
    }

    /**
     * Returns whether or not adapters should autoadd themselves to the registry
     *
     * @return bool
     */
    public static function getAutoAdd()
    {
        return self::$bAutoAdd;
    }

    /**
     * Returns all registered adapters so far
     *
     * @return array
     */
    public static function getRegisteredAdapters()
    {
        return self::$aAdapters;
    }

    /**
     * Returns the registered adapter by name, or the first one if no name is given.
     *
     * @param null|string $mInput
     *
     * @return adapters\MysqlAdapter|adapters\PgsqlAdapter
     * @throws DatabaseRegistryException
     */
    public static function getAdapter($mInput = null)
    {
        if (empty(self::$aAdapters)) {
            throw new DatabaseRegistryException('No adapters have been registered yet');
        }
        if ($mInput === null) {
            foreach (self::$aAdapters as $sName => &$mValue) {
                $mInput = $sName;
                break;
            }
        } else if (isset(self::$aAdapters[$mInput])) {
            $mValue = self::$aAdapters[$mInput];
        } else {
            throw new DatabaseRegistryException('No such adapter found in Registry: ' . $mInput);
        }

        if (isset($mValue)) {
            if (is_array($mValue)) {
                switch (strtolower($mValue['dbType'])) {
                    case 'mysql':
                        $oAdapter = new \flyingpiranhas\common\database\adapters\MysqlAdapter($mValue);
                        break;
                    case 'pgsql':
                        $oAdapter = new \flyingpiranhas\common\database\adapters\PgsqlAdapter($mValue);
                        break;
                    default:
                        throw new DatabaseRegistryException('Unable to use dbType ' . $mValue['dbType']);
                        break;
                }
                self::$aAdapters[$mInput] = $oAdapter;
            } else if ($mValue instanceof AdapterInterface) {
                $oAdapter = $mValue;
            } else {
                throw new DatabaseRegistryException(
                        'Invalid data found. Registration is neither array nor adapter.'
                );
            }

            return $oAdapter;
        } else {
            throw new DatabaseRegistryException('No such adapter found in Registry: ' . $mInput);
        }
    }

}

/**
 * Thrown when there's a problem with the Database Registry
 *
 * @category       database
 * @package        flyingpiranhas.common
 * @license        Apache-2.0
 * @version        0.01
 * @since          2012-11-24
 * @author         Bruno Škvorc <bruno@skvorc.me>
 */
class DatabaseRegistryException extends FpException
{

}
