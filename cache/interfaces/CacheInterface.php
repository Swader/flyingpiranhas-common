<?php

    namespace flyingpiranhas\common\cache\interfaces;

    /**
     * Any cache object expected to be used by the flyingpiranhas components
     * should conform to this interface.
     *
     * @author pinetree
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
