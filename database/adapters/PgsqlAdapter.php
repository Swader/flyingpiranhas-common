<?php

    namespace flyingpiranhas\common\database\adapters;

    /**
     * The PgsqlAdapter is a derivate of the Adapter Abstract used
     * solely for connecting to PostgreSql.
     *
     * @category       database
     * @package        flyingpiranhas.common
     * @license        BSD License
     * @version        0.01
     * @since          2012-11-23
     * @author         pinetree
     * @author         Bruno Škvorc <bruno@skvorc.me>
     */
    class PgsqlAdapter extends \flyingpiranhas\common\database\abstracts\AdapterAbstract
    {
        /** @var int The database port, defaults to 5432 for PgSQL */
        protected $iDbPort = 5432;

        /** @var string Database type */
        protected $sDbType = 'pgsql';

        /** @var string Special pgsql related property */
        protected $sIdSequenceSuffix = "_id_seq";

        //===== QUERY HELPERS =====//

        /**
         * Quotes a single string
         * Overrides parent method to account for PostgreSql's handling of bool quoting
         *
         * @param string $sValue
         *
         * @return string
         */
        protected function quoteString($sValue)
        {
            if (is_bool($sValue)) {
                return ($sValue) ? 'TRUE' : 'FALSE';
            }

            return parent::quoteString($sValue);
        }

        /**
         * Returns the id of the row last inserted in the database or the given table
         * Since PostgreSql does not support auto incremented id-s, sequences must be used
         *
         * @param string $sTable DEF: '';
         *
         * @return int
         */
        public function lastInsertId($sTable = '')
        {
            return parent::lastInsertId($sTable . $this->sIdSequenceSuffix);
        }

    }
