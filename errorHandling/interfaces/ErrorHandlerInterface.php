<?php

namespace flyingpiranhas\common\errorHandling\interfaces;

use Exception;
use ErrorException;

/**
 * Any ErrorHandler that will be used by FP components
 * should implement this interface.
 *
 * @category       errorHandling
 * @package        flyingpiranhas.common
 * @license        Apache-2.0
 * @version        0.01
 * @since          2012-11-23
 * @author         Ivan Pintar
 */
interface ErrorHandlerInterface
{

    /**
     * The method sent to PHP's set_error_handler()
     *
     * @param int    $iErrno
     * @param string $sErrstr
     * @param string $sErrfile
     * @param string $sErrline
     *
     * @throws ErrorException
     */
    public function handleErrors($iErrno, $sErrstr, $sErrfile, $sErrline);

    /**
     * The method sent to PHP's set_exception_handler()
     *
     * @param Exception $exception
     *
     * @return mixed
     */
    public function handleExceptions(Exception $exception);

    /**
     * The method sent to PHP's register_shutdown_function()
     */
    public function handleFatals();

    /**
     * Registers itself as the PHP error handler
     */
    public function register();
}