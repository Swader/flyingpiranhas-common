<?php

namespace flyingpiranhas\common\utils;

use flyingpiranhas\common\exceptions\FpException;

/**
 * The validator class is used to validate certain values like
 * user input, URLs, etc.
 *
 * Note: urlExists requires you to have curl installed.
 *
 * @category       Utilities
 * @package        flyingpiranhas.common
 * @license        BSD License
 * @version        0.01
 * @since          2012-09-07
 * @author         Bruno Škvorc <bruno@skvorc.me>
 */
class Validator
{

    /**
     * Validates a URL.
     * If curl is not installed, throws exception.
     *
     * @param $url
     *
     * @throws FpException
     *
     * @return bool
     */
    public static function urlExists($url)
    {
        if (!function_exists('curl_init')) {
            $e = new FpException('You need to have curl installed to use this method!');
            throw $e->setUserFriendlyMessage(
                    'Missing PHP extension (curl) - please contact the website administrator!'
            );
        }

        // Version 4.x supported
        $rHandle = curl_init($url);
        if (false === $rHandle) {
            return false;
        }
        curl_setopt($rHandle, CURLOPT_HEADER, false);
        curl_setopt($rHandle, CURLOPT_FAILONERROR, true); // this works
        curl_setopt(
                $rHandle, CURLOPT_HTTPHEADER, Array("User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.15) Gecko/20080623 Firefox/2.0.0.15")
        ); // request as if Firefox
        curl_setopt($rHandle, CURLOPT_NOBODY, true);
        curl_setopt($rHandle, CURLOPT_RETURNTRANSFER, false);
        curl_exec($rHandle);
        $aUrlInfo = curl_getinfo($rHandle);
        curl_close($rHandle);
        if ($aUrlInfo['http_code'] == 302) {
            return self::urlExists($aUrlInfo['redirect_url']);
        } else {
            return ($aUrlInfo['http_code'] == 200) ? true : false;
        }
    }

    /**
     * Checks if input is a MySQL nulldate
     *
     * @param string $sInput
     *
     * @return bool
     * @throws \InvalidArgumentException
     */
    public static function dateIsNull($sInput)
    {
        if (!is_string($sInput)) {
            throw new \InvalidArgumentException('Input needs to be string');
        }

        return (
                $sInput == '0000-00-00'
                || $sInput == '0000-00-00 00:00:00'
                || strpos($sInput, '0000-00-00') !== false
                || empty($sInput)
                || $sInput === null
                );
    }

    /**
     * Validates an email address.
     * If $bCheckDomain is true, the MY records of the domain are checked too.
     * This is generally not recommended, as it's easier to check if an email is
     * valid with a confirmation message.
     *
     * @param string $sEmail
     * @param bool   $bCheckDomain
     *
     * @return bool
     */
    public static function validateEmail($sEmail, $bCheckDomain = false)
    {
        $rRegex = "/[a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?/";
        $mFilter = filter_var($sEmail, FILTER_VALIDATE_REGEXP, array("options" => array("regexp" => $rRegex)));

        if ($bCheckDomain) {
            list($user, $domain) = explode('@', $sEmail);
            $valid = self::validateDomainByRecordType($domain) && $mFilter;
        } else {
            $valid = (bool) $mFilter;
        }

        return $valid;
    }

    /**
     * Validates a domain. If the domain doesn't exist or gives no response, returns false.
     *
     * @param string $sDomain
     * @param string $sRecord
     *
     * @return bool
     */
    public static function validateDomainByRecordType($sDomain, $sRecord = 'mx')
    {
        return checkdnsrr($sDomain, $sRecord);
    }

}
