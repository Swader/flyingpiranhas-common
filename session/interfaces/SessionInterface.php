<?php

    namespace flyingpiranhas\common\session\interfaces;

    /**
     * Any Session object that is expected to be used by other Flyingpiranhas components
     * should implement this interface, to allow for easy registering with the DIContainer.
     *
     * @author pinetree
     */
    interface SessionInterface extends \IteratorAggregate, \ArrayAccess
    {
        public function registerAndStart();
    }
