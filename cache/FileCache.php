<?php

namespace flyingpiranhas\common\cache;

use flyingpiranhas\common\cache\interfaces\CacheInterface;
use flyingpiranhas\common\cache\exceptions\CacheException;

/**
 * The file cache class writes cached content to a temporary file.
 *
 * @category       database
 * @package        flyingpiranhas.common
 * @license        Apache-2.0
 * @version        0.01
 * @since          2012-11-24
 * @author         Ivan Pintar
 * @author         Bruno Škvorc <bruno@skvorc.me>
 */
class FileCache implements CacheInterface
{

    /** @var string */
    private $sCachePrefix = 'flyingpiranhasCache';

    /** @var array|mixed */
    private $aCacheStore = array();

    /** @var string */
    private $sFilePath = '/tmp/fpCache.txt';

    /**
     * Accepts a single argument telling the class where to write the cache data.
     * If location is not readable, throws exception. If file already exists, contents
     * are prepended to cacheStore and new content will be appended.
     */
    public function __construct($sFilePath = null)
    {
        if (!empty($sFilePath)) {
            $this->sFilePath = $sFilePath;
        }

        if (is_readable($this->sFilePath)) {
            $this->aCacheStore = json_decode(file_get_contents($this->sFilePath), true);
        } else {
            if (file_put_contents($this->sFilePath, '') === false) {
                throw new CacheException('Cannot write to location: ' . $this->sFilePath);
            }
        }
    }

    /**
     * @param string $sKey
     * @param mixed  $mValue
     * @param int    $iLifeTime
     *
     * @return int|bool Returns number of written bytes or false on failure
     */
    public function set($sKey, $mValue, $iLifeTime = 0)
    {
        $sKey                     = $this->sCachePrefix . $sKey;
        $this->aCacheStore[$sKey] = $mValue;

        return file_put_contents($this->sFilePath, json_encode($this->aCacheStore));
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
     *
     * @return int|bool Returns number of written bytes or false on failure
     */
    public function delete($sKey)
    {
        $sKey = $this->sCachePrefix . $sKey;
        unset($this->aCacheStore[$sKey]);

        return file_put_contents($this->sFilePath, json_encode($this->aCacheStore));
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

    /**
     * @return int
     */
    public function clear()
    {
        $this->aCacheStore = array();
        return file_put_contents($this->sFilePath, json_encode($this->aCacheStore));
    }

}
