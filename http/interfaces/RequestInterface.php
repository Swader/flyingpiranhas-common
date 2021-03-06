<?php

namespace flyingpiranhas\common\http\interfaces;

/**
 * A Request object which is expected to be used by other FP components,
 * should implement this interface
 *
 * @category       http
 * @package        flyingpiranhas.http
 * @license        Apache-2.0
 * @version        0.01
 * @since          2012-09-07
 * @author         Ivan Pintar
 */
interface RequestInterface
{

    /**
     * @return array
     */
    public function getServer();

    /**
     * @return array
     */
    public function getParams();

    /**
     * @return array
     */
    public function getCookies();

}