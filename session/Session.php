<?php

namespace flyingpiranhas\common\session;

use SessionHandlerInterface;
use ArrayIterator;
use flyingpiranhas\common\session\exceptions\SessionException;
use flyingpiranhas\common\session\interfaces\SessionInterface;

/**
 * The Session class is used to enable easy and natural API access to the
 * SESSION superglobal.
 *
 * Using this class, one is able to activate session management via mysql or postgresql
 * through a single line of code, simply by passing in the appropriate handler in the
 * constructor.
 *
 * @category       session
 * @package        flyingpiranhas.common
 * @license        BSD License
 * @version        0.01
 * @since          2012-11-23
 * @author         Ivan Pintar
 * @author         Bruno Škvorc <bruno@skvorc.me>
 */
class Session implements SessionInterface
{

    /** @var SessionHandlerInterface */
    protected $oSessionHandler;

    /** @var bool */
    protected static $bSessionStarted = false;

    /** @var bool */
    protected static $bSessionRegenerated = false;

    /**
     * Creates a new session object.
     * If the session handler is given it will set it as the session handler
     *
     * @param SessionHandlerInterface $oSessionHandler
     */
    public function __construct(SessionHandlerInterface $oSessionHandler)
    {
        $this->oSessionHandler = $oSessionHandler;
    }

    /**
     * Registers the defined session handler (default native is used if no session handler given)
     * and starts the current session
     */
    public function registerAndStart()
    {
        if (!self::$bSessionStarted) {
            session_set_save_handler(
                array($this->oSessionHandler, 'open'),
                array($this->oSessionHandler, 'close'),
                array($this->oSessionHandler, 'read'),
                array($this->oSessionHandler, 'write'),
                array($this->oSessionHandler, 'destroy'),
                array($this->oSessionHandler, 'gc')
            );

            self::$bSessionStarted = session_start();
            if (!self::$bSessionStarted) {
                throw new SessionException('Session failed to start');
            }
        }

        $this->regenerateId();

        return self::$bSessionStarted;
    }

    /**
     * Regenerates session ID, deletes old session if input param is true
     *
     * @param bool $bDeleteOld
     *
     * @return bool
     */
    public function regenerateId($bDeleteOld = true)
    {
        if (!self::$bSessionRegenerated) {
            self::$bSessionRegenerated = true;

            //return session_regenerate_id($bDeleteOld);
        }

        return true;
    }

    /**
     * Destroys a session and deletes cookie if param is set to true
     *
     * @param bool $bDestroyCookie
     *
     * @return bool
     */
    public function destroy($bDestroyCookie = false)
    {
        if (self::$bSessionStarted && session_destroy()) {
            return true;
        }

        if (!self::$bSessionStarted) {
            return true;
        }

        if (!$bDestroyCookie || ($bDestroyCookie && $this->expireCookie())) {
            return true;
        }

        return false;
    }

    /**
     * Destroys the session cookie
     *
     * @return bool
     */
    public function expireCookie()
    {
        if (isset($_COOKIE[session_name()])) {
            $cookieParams = session_get_cookie_params();

            return setcookie(
                session_name(),
                false, strtotime('2000-01-01'),
                $cookieParams['path'],
                $cookieParams['domain'],
                $cookieParams['secure']
            );
        }
        return true;
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($_SESSION);
    }

    /**
     * @param string $sKey
     *
     * @return bool
     */
    public function offsetExists($sKey)
    {
        return (isset($_SESSION[$sKey]));
    }

    /**
     * @param string $sKey
     *
     * @return mixed
     */
    public function offsetGet($sKey)
    {
        return (isset($_SESSION[$sKey])) ? $_SESSION[$sKey] : null;
    }

    /**
     * @param string|null $sKey
     * @param mixed       $mValue
     */
    public function offsetSet($sKey, $mValue)
    {
        $_SESSION[$sKey] = $mValue;
    }

    /**
     * @param string $sKey
     */
    public function offsetUnset($sKey)
    {
        unset($_SESSION[$sKey]);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $_SESSION;
    }

    /**
     * @param string $sName
     *
     * @return mixed
     */
    public function __get($sName)
    {
        return $this->offsetGet($sName);
    }

    /**
     * @param string $sName
     * @param mixed  $mValue
     */
    public function __set($sName, $mValue)
    {
        $this->offsetSet($sName, $mValue);
    }

    /**
     * @param string $sName
     *
     * @return bool
     */
    public function __isset($sName)
    {
        return isset($_SESSION[$sName]);
    }

    /**
     * Kill method to prevent cloning
     *
     * @throws SessionException
     */
    public function __clone()
    {
        throw new SessionException('Cannot clone session');
    }

}
