<?php

namespace flyingpiranhas\common\database\adapters;

use flyingpiranhas\common\database\abstracts\AdapterAbstract;

/**
 * The MysqlAdapter is a derivate of the Adapter Abstract used
 * solely for connecting to MySql.
 *
 * @category       database
 * @package        flyingpiranhas.common
 * @license        Apache-2.0
 * @version        0.01
 * @since          2012-11-23
 * @author         Ivan Pintar
 * @author         Bruno Škvorc <bruno@skvorc.me>
 */
class MysqlAdapter extends AdapterAbstract
{

    /** @var int Database port, defaults to 3306 for Mysql */
    protected $iDbPort = 3306;

    /** @var string Database type */
    protected $sDbType = 'mysql';

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
            return (int) $sValue;
        }

        return parent::quoteString($sValue);
    }

}
