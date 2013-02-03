<?php

namespace flyingpiranhas\common\session\interfaces;

use flyingpiranhas\common\database\interfaces\AdapterInterface;
use SessionHandlerInterface;

/**
 * Provides a contract for all database driven session handlers
 *
 * @category       database
 * @package        flyingpiranhas.common
 * @license        BSD License
 * @version        0.01
 * @since          2012-11-23
 * @author         Bruno Škvorc <bruno@skvorc.me>
 */
interface DbSessionHandlerInterface extends SessionHandlerInterface
{

    /**
     * Sets the adapter to be used as the persistence layer of the session handler
     * @param AdapterInterface|string $oAdapter
     *
     * @return mixed
     */
    function setAdapter($oAdapter);

    /** Returns the adapter used for persistence */
    function getAdapter();

}
