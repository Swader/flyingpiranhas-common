<?php

    namespace flyingpiranhas\common;

    /**
     * The version class serves as a way to easily identify which version of
     * fpcommon you have. This class is used by FlyingPiranhas MVC to verify that the
     * required components are accessible and present.
     *
     * Do not change the version number manually. Doing so may result in general application
     * instability.
     *
     * @category       common
     * @package        flyingpiranhas
     * @license        Apache-2.0
     * @version        0.01
     * @since          2012-11-23
     * @author         Bruno Škvorc <bruno@skvorc.me>
     */
    class Version
    {
        /** @var float Version number */
        protected static $VERSION = 0.1;

        /**
         * Returns the floating point number representing the current
         * fpcommon version.
         *
         * @return float
         */
        public static function getVersion() {
            return self::$VERSION;
        }
    }
