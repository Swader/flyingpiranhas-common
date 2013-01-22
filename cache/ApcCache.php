<?php

    namespace flyingpiranhas\common\cache;
    use flyingpiranhas\common\cache\interfaces\CacheInterface;


    /**
     * The ApcCache provides a wrapper around the APC caching mechanism.
     *
     * @author pinetree
     */
    class ApcCache implements CacheInterface
    {
        /** @var string */
        private $sCachePrefix = 'flyingpiranhasCache';

        /**
         * @param string $sKey
         * @param mixed  $mValue
         * @param int    $iLifeTime
         */
        public function set($sKey, $mValue, $iLifeTime = 0)
        {
            apc_store($this->sCachePrefix . $sKey, $mValue, (int)$iLifeTime);
        }

        /**
         * @param string $sKey
         *
         * @return mixed
         */
        public function get($sKey)
        {
            return apc_fetch($this->sCachePrefix . $sKey);
        }

        /**
         * @param string $sKey
         */
        public function delete($sKey)
        {
            apc_delete($this->sCachePrefix . $sKey);
        }

        /**
         * @param string $sKey
         *
         * @return bool
         */
        public function exists($sKey)
        {
            return apc_exists($this->sCachePrefix . $sKey);
        }

        /**
         * Clears all APC cached data
         *
         * @return bool
         */
        public function clear()
        {
            return (apc_clear_cache() && apc_clear_cache('user'));
        }

    }
