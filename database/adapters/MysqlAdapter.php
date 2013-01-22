<?php

    namespace flyingpiranhas\common\database\adapters;

    /**
     * The MysqlAdapter is a derivate of the Adapter Abstract used
     * solely for connecting to MySql.
     *
     * @category       database
     * @package        flyingpiranhas.common
     * @license        BSD License
     * @version        0.01
     * @since          2012-11-23
     * @author         pinetree
     * @author         Bruno Škvorc <bruno@skvorc.me>
     */
    class MysqlAdapter extends \flyingpiranhas\common\database\abstracts\AdapterAbstract
    {

        /** @var int Database port, defaults to 3306 for Mysql */
        protected $iDbPort = 3306;

        /** @var string Database type */
        protected $sDbType = 'mysql';

        //===== QUERY HELPERS =====//

        /**
         * Quotes a single string
         * Overrides parent method to account for MySql's handling of bool quoting
         *
         * @param string $sValue
         *
         * @return string
         */
        protected function quoteString($sValue)
        {
            if (is_bool($sValue)) {
                return (int)$sValue;
            }

            return parent::quoteString($sValue);
        }

    }
