<?php

namespace flyingpiranhas\common\http\cookies;
use IteratorAggregate;
use InvalidArgumentException;
use ArrayIterator;

/**
 * The CookieRoot wraps the $_COOKIE superglobal into an easy to use iterable object,
 * and turns the elements of the $_COOKIE array into Cookie objects.
 *
 * @category       http
 * @package        flyingpiranhas.http
 * @license        BSD License
 * @version        0.01
 * @since          2012-09-07
 * @author         Ivan Pintar
 */
class CookieJar implements IteratorAggregate
{

    /** @var array */
    private $aCookies = array();

    /**
     * @param array $aCookies
     */
    public function __construct(array $aCookies)
    {
        /** @var $oCookie Cookie */
        foreach ($aCookies as $oCookie) {
            $this->aCookies[$oCookie->getName()] = $oCookie;
        }
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->aCookies);
    }

    /**
     * @param string $sName
     * @return null
     */
    public function __get($sName)
    {
        return isset($this->aCookies[$sName]) ? $this->aCookies[$sName] : null;
    }

    /**
     * @param string $sName
     * @param Cookie|null $mValue
     * @throws InvalidArgumentException
     */
    public function __set($sName, $mValue)
    {
        if (!$mValue instanceof Cookie) {
            throw new InvalidArgumentException("Param {$mValue} is not a valid Cookie");
        }

        $this->aCookies[$sName] = $mValue;
    }

    /**
     * @param string $sName
     *
     * @return bool
     */
    public function __isset($sName)
    {
        return isset($this->aParams[$sName]);
    }

}
