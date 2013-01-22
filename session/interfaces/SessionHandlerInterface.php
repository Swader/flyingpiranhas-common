<?php

    namespace flyingpiranhas\common\session\interfaces;

    /**
     * Dictates the look and feel of session handlers
     *
     * @category       database
     * @package        flyingpiranhas.common
     * @license        BSD License
     * @version        0.01
     * @since          2012-11-23
     * @author         Bruno Škvorc <bruno@skvorc.me>
     */
    interface SessionHandlerInterface extends \SessionHandlerInterface
    {
        /**
         * Sets the adapter to be used as the persistence layer of the session handler
         * @param \flyingpiranhas\common\database\interfaces\AdapterInterface|string $oAdapter
         *
         * @return mixed
         */
        function setAdapter($oAdapter);

        /** Returns the adapter used for persistence */
        function getAdapter();
    }
