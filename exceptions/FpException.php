<?php

namespace flyingpiranhas\common\exceptions;

/**
 * The Flyingpiranhas Exception is the general exception used in
 * all Flyingpiranhas components/libraries/packages. The one extra
 * feature it always has is the setter and getter for
 * userFriendlyMessage(), which gives you the opportunity to display
 * a casual error to the visitor, should it ever occur.
 *
 * @category       exceptions
 * @package        flyingpiranhas.common
 * @license        Apache-2.0
 * @version        0.01
 * @since          2012-09-07
 * @author         Bruno Škvorc <bruno@skvorc.me>
 */
class FpException extends \Exception
{

    /** @var string */
    protected $sUserFriendlyMessage;

    /**
     * Returns the user friendly message
     *
     * @return string
     */
    public function getUserFriendlyMessage()
    {
        return $this->sUserFriendlyMessage;
    }

    /**
     * Sets the user friendly message
     * Returns the Exception instance for easier throwing and chaining
     *
     * @param string $sText
     *
     * @return FpException
     */
    public function setUserFriendlyMessage($sText)
    {
        $this->sUserFriendlyMessage = $sText;
        return $this;
    }

}
