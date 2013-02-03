<?php

namespace flyingpiranhas\common\cache\interfaces;

/**
 * @category       cache
 * @package        flyingpiranhas.common
 * @license        BSD License
 * @version        0.01
 * @since          2012-11-23
 * @author         Ivan Pintar
 */
interface CacheInterface
{

    /**
     * @param string $sKey
     * @param mixed  $mValue
     * @param int    $iLifeTime
     */
    public function set($sKey, $mValue, $iLifeTime = 0);

    /**
     * @param string $sKey
     *
     * @return mixed
     */
    public function get($sKey);

    /**
     * @param string $sKey
     */
    public function delete($sKey);

    /**
     * @param string $sKey,
     */
    public function exists($sKey);

    public function clear();

}
