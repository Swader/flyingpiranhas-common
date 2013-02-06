<?php

namespace flyingpiranhas\common\config\interfaces;

/**
 * Any object that is to be used by FP components as a ConfigRoot object
 * should implement this interface.
 *
 * @category       config
 * @package        flyingpiranhas.common
 * @license        Apache-2.0
 * @version        0.01
 * @since          2012-11-23
 * @author         Ivan Pintar
 */
interface ConfigRootInterface
{

    /**
     * @param string $sIniPath
     *
     * @return ConfigRootInterface
     */
    public function parseConfigFile($sIniPath);

}