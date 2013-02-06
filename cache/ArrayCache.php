<?php

namespace flyingpiranhas\common\cache;

use flyingpiranhas\common\cache\interfaces\CacheInterface;

/**
 * The ArrayCache is a dummy caching wrapper which implements a simple array as the cache storage.
 * It actually does not cache anything, as the array is dynamic. This is to be used in a development
 * environment where frequent changes could cause a real cache to become stale very quickly and very often.
 *
 * @category       cache
 * @package        flyingpiranhas.common
 * @license        Apache-2.0
 * @version        0.01
 * @since          2012-11-23
 * @author         Ivan Pintar
 */
class ArrayCache implements CacheInterface
{

    /** @var string */
    private $sCachePrefix = 'flyingpiranhasCache';

    /** @var array */
    private $aCacheStore = array();

    /**
     * @param string $sKey
     * @param mixed  $mValue
     * @param int    $iLifeTime
     */
    public function set($sKey, $mValue, $iLifeTime = 0)
    {
        $sKey                     = $this->sCachePrefix . $sKey;
        $this->aCacheStore[$sKey] = $mValue;
    }

    /**
     * @param string $sKey
     *
     * @return mixed
     */
    public function get($sKey)
    {
        $sKey = $this->sCachePrefix . $sKey;

        return $this->aCacheStore[$sKey];
    }

    /**
     * @param string $sKey
     */
    public function delete($sKey)
    {
        $sKey = $this->sCachePrefix . $sKey;
        unset($this->aCacheStore[$sKey]);
    }

    /**
     * @param string $sKey
     *
     * @return bool
     */
    public function exists($sKey)
    {
        $sKey = $this->sCachePrefix . $sKey;

        return isset($this->aCacheStore[$sKey]);
    }

    public function clear()
    {
        $this->aCacheStore = array();
    }

}
