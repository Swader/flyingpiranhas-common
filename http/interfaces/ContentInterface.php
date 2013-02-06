<?php

namespace flyingpiranhas\common\http\interfaces;

/**
 * @category       http
 * @package        flyingpiranhas.http
 * @license        Apache-2.0
 * @version        0.01
 * @since          2012-09-07
 * @author         Ivan Pintar
 */
interface ContentInterface
{

    /**
     * @return array
     */
    public function getResponseHeaders();

    public function render();
}