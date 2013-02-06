<?php

namespace flyingpiranhas\common\errorHandling;

use flyingpiranhas\common\errorHandling\interfaces\ErrorHandlerInterface;
use ErrorException;
use Exception;

/**
 * The basic error handler. This turns notices and warnings into exceptions,
 * so they can be dealt with in an OO manner using try/catch blocks.
 *
 * @category       dependencyInjection
 * @package        flyingpiranhas.common
 * @license        Apache-2.0
 * @version        0.01
 * @since          2012-11-23
 * @author         Ivan Pintar
 */
class ErrorHandler implements ErrorHandlerInterface
{

    /**
     * The method sent to PHP's set_error_handler()
     *
     * @param int $iErrno
     * @param string $sErrstr
     * @param string $sErrfile
     * @param string $sErrline
     *
     * @throws ErrorException
     */
    public function handleErrors($iErrno, $sErrstr, $sErrfile, $sErrline)
    {
        throw new ErrorException($sErrstr, $iErrno, 1, $sErrfile, $sErrline);
    }

    /**
     * The method sent to PHP's set_exception_handler()
     *
     * @param Exception $oException
     *
     * @return mixed|void
     * @throws Exception
     */
    public function handleExceptions(Exception $oException)
    {
        throw $oException;
    }

    /**
     * The method sent to PHP's register_shutdown_function()
     */
    public function handleFatals()
    {
        $aLastError = error_get_last();
        if ($aLastError) {
            $this->handleErrors($aLastError['type'], $aLastError['message'], $aLastError['file'], $aLastError['line']);
        }
    }

    /**
     * Registers itself as the PHP error handler
     */
    public function register()
    {
        set_error_handler(array($this, 'handleErrors'));
        set_exception_handler(array($this, 'handleExceptions'));
        register_shutdown_function(array($this, 'handleFatals'));
    }

}

