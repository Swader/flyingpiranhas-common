<?php
    namespace flyingpiranhas\common\database\interfaces;

    /**
     * Defines some required database adapter functions
     *
     * @category       common
     * @package        flyingpiranhas
     * @license        BSD License
     * @version        0.01
     * @since          2012-11-23
     * @author         Bruno Škvorc <bruno@skvorc.me>
     */
    interface AdapterInterface
    {
        function testConnection();
        function getAdapterName();
        /** Sets a unique adapter name */
        function setAdapterName($sName);
        /** Executes a query. Returns result set if select, or affected record count otherwise */
        function query($sQuery, $aBindParams = array(), $mFetchMode = 1);
    }
