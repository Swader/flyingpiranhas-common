<?php

namespace flyingpiranhas\common\session\interfaces;

/**
 * Any Session object that is expected to be used by other Flyingpiranhas components
 * should implement this interface, to allow for easy registering with the DIContainer.
 *
 * @category       session
 * @package        flyingpiranhas.common
 * @license        Apache-2.0
 * @version        0.01
 * @since          2012-09-07
 * @author         Bruno Škvorc <bruno@skvorc.me>
 */
interface SessionInterface extends \IteratorAggregate, \ArrayAccess
{

    public function registerAndStart();

}
