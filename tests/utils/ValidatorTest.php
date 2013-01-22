<?php

    namespace flyingpiranhas\common\tests\utils;

    use flyingpiranhas\common\utils\Validator;

    /**
     * Class ValidatorTest.php
     *
     * @category      Tests
     * @package       Utils
     * @license       BSD License
     * @version       0.01
     * @since         2012-10-07
     * @author        Bruno Škvorc <bruno@skvorc.me>
     */
    class ValidatorTest extends \PHPUnit_Framework_TestCase
    {

        public function testDateIsNull() {
            $this->assertFalse(Validator::dateIsNull('2012-06-20'));
            $this->assertTrue(Validator::dateIsNull(''));
            $this->assertTrue(Validator::dateIsNull('0000-00-00 00:00:00'));
            $this->assertTrue(Validator::dateIsNull('0000-00-00'));
            $this->assertTrue(Validator::dateIsNull('0000-00-00 02:30:20'));
            $this->assertFalse(Validator::dateIsNull('2012-06-20 00:00:00'));

            $this->setExpectedException('InvalidArgumentException');
            $this->assertTrue(Validator::dateIsNull(null));
        }

        public function testHasCurlException() {
            if (!function_exists('curl_init')) {
                $this->setExpectedException('\flyingpiranhas\common\exceptions\FpException');
                Validator::urlExists('http://www.google.com');
            } else {
                $this->assertTrue(function_exists('curl_init'));
            }
        }

        public function testExistingUrl() {
            $this->assertTrue(Validator::urlExists('http://www.google.com'));
        }

        public function testNonExistingUrl() {
            $this->assertFalse(Validator::urlExists('http://www.jumblemumbledomainthatisguaranteednottoexistblahblahblah.com'));
        }

    }
