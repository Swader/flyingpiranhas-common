<?php

    namespace flyingpiranhas\common\utils;

    /**
     * The Format class can format values into other output formats.
     * It's useful for currency conversion, numeric formatting (currency,
     * memory, percentages...), date conversion etc.
     *
     * @category       Utilities
     * @package        flyingpiranhas.common
     * @license        BSD License
     * @version        0.01
     * @since          2012-09-07
     * @author         Bruno Škvorc <bruno@skvorc.me>
     */
    class Format
    {

        /**
         * Returns a byte size in human readable format.
         * Taken from John Himmelman's answer here:
         * http://stackoverflow.com/questions/2510434/php-format-bytes-to-kilobytes-megabytes-gigabytes
         *
         * @param int $size Amount of bytes
         * @param int $precision
         *
         * @return string
         * @throws \InvalidArgumentException
         *
         * @since          2012-09-07
         * @author         Bruno Škvorc <bruno@skvorc.me>
         * @author         John Himmelman
         */
        public static function formatBytes($size, $precision = 3)
        {
            if ($precision < 0) {
                throw new \InvalidArgumentException('Precision needs to be 0 or positive integer');
            }
            if ($size < 0) {
                throw new \InvalidArgumentException('Size cannot be negative.');
            }
            if (!$size) {
                return 0;
            }
            $base = log($size) / log(1024);
            $suffixes = array('b', 'kb', 'Mb', 'Gb', 'Tb');

            $iSuffix = floor($base);
            return round(pow(1024, $base - floor($base)), $precision) . " " . $suffixes[(int)$iSuffix];
        }

        /**
         * Returns numeric timestamp since Unix Epoch regardless of input
         * parameter type
         *
         * @param mixed $mInput
         *
         * @return int
         * @throws \InvalidArgumentException
         */
        public static function toTime($mInput = null)
        {
            $iResult =
                (is_numeric($mInput))
                    ? (int)$mInput
                    : (
                (is_string($mInput))
                    ? strtotime($mInput)
                    : (($mInput === null) ? time() : false)
                );

            if ($iResult === false || (is_numeric($mInput) && $mInput < 0)) {
                throw new \InvalidArgumentException('Invalid input: ' . $mInput);
            } else {
                return $iResult;
            }
        }

        /**
         * Converts a value into a human readable date format
         *
         * @param mixed $mInput
         *
         * @return string
         */
        public static function toReadableDate($mInput = null)
        {
            return date('M d, Y', self::toTime($mInput));
        }

        /**
         * Converts a value into a Mysql Datetime compatible format
         * Set full to true to get the time alongside the date
         *
         * @param null $mInput
         * @param bool $bFull
         *
         * @return string
         */
        public static function toMysqlDate($mInput = null, $bFull = false)
        {
            $sFormat = 'Y-m-d';
            $sFormat .= ($bFull) ? ' H:i:s' : '';
            return date($sFormat, self::toTime($mInput));
        }
    }
