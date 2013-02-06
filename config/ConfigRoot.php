<?php

namespace flyingpiranhas\common\config;

use flyingpiranhas\common\config\interfaces\ConfigRootInterface;
use flyingpiranhas\common\config\exceptions\ConfigException;

/**
 * The ConfigRoot object is a Config extension with the ability to parse
 * an ini file, and turn it into Config objects.
 *
 * @category       config
 * @package        flyingpiranhas.common
 * @license        Apache-2.0
 * @version        0.01
 * @since          2012-11-23
 * @author         Ivan Pintar
 */
class ConfigRoot extends Config implements ConfigRootInterface
{

    /**
     * Parses the given config file (if any) and builds a tree of Config objects
     *
     * @param string        $sAppEnv
     * @param bool|string   $sConfigFilePath
     */
    public function __construct($sAppEnv = 'production', $sConfigFilePath = false)
    {
        $this->appEnv = $sAppEnv;

        if ($sConfigFilePath) {
            $this->parseConfigFile($sConfigFilePath, $sAppEnv);
        }
    }

    /**
     * @param string $sIniPath
     *
     * @return ConfigRoot
     */
    public function parseConfigFile($sIniPath)
    {
        $aIniArray = parse_ini_file($sIniPath, true);
        foreach ($aIniArray as $mKey => &$mIniRow) {

            if (!is_array($mIniRow)) {
                $this->parseIniRow($mKey, $mIniRow, $this);
            } else {
                foreach ($mIniRow as $sRowKey => &$sRowValue) {
                    $this->parseIniRow($sRowKey, $sRowValue, $this);
                }
            }

            if ($mKey == $this->appEnv) {
                break;
            }
        }
        return $this;
    }

    /**
     * @param $sKey
     * @param $sValue
     * @param $oObject
     *
     * @return ConfigRoot
     * @throws ConfigException
     */
    private function parseIniRow($sKey, $sValue, $oObject)
    {
        if (!is_object($oObject)) {
            throw new ConfigException('Third parameter is not a valid object');
        }

        $sValue = (strtolower($sValue) == 'false') ? false : $sValue;
        $sValue = (strtolower($sValue) == 'true') ? true : $sValue;
        $sValue = (strtolower($sValue) == 'null') ? null : $sValue;

        $aKey = explode('.', $sKey);
        if (count($aKey) <= 1) {
            $oObject->$sKey = $sValue;
            return $this;
        }

        $sSettingKey = $aKey[0];
        if (!isset($oObject->$sSettingKey)) {
            $oObject->$sSettingKey = new Config();
        }
        unset($aKey[0]);

        $sKey = implode('.', $aKey);
        $this->parseIniRow($sKey, $sValue, $oObject->$sSettingKey);

        return $this;
    }

}
