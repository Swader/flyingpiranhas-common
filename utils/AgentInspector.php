<?php

namespace flyingpiranhas\common\utils;

/**
 * The AgentInspector provides information about the site visitor
 *
 * @todo Loads of missing functionality, needs to be added. Anyone, feel free to chip in
 *
 * @category       utils
 * @package        flyingpiranhas.common
 * @license        Apache-2.0
 * @version        0.01
 * @since          2012-11-24
 * @author         Bruno Škvorc <bruno@skvorc.me>
 */
class AgentInspector
{

    /**
     * Returns the IP of the current visitor
     *
     * @return string
     */
    public static function getIp()
    {
        return (string) ((getenv("HTTP_X_FORWARDED_FOR")) ? getenv("HTTP_X_FORWARDED_FOR") : getenv("REMOTE_ADDR"));
    }

    /**
     * Returns the browser that executed the request
     *
     * @return mixed
     */
    public static function getAgent()
    {
        return $_SERVER['HTTP_USER_AGENT'];
    }

}
