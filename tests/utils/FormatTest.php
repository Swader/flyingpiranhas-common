<?php

    namespace flyingpiranhas\common\tests\utils;

    use flyingpiranhas\common\utils\Format;

    /**
     * Class FormatTest.php
     *
     * @category      Tests
     * @package       Utils
     * @license       BSD License
     * @version       0.01
     * @since         2012-10-07
     * @author        Bruno Škvorc <bruno@skvorc.me>
     */
    class FormatTest extends \PHPUnit_Framework_TestCase
    {

        /** @var Format */
        protected $oFormatter;

        public function setUp()
        {
            $this->oFormatter = new Format;
        }

        public function testFormatBytes()
        {
            $aPrecisions = array(1, 2, 3, 4, 5, 6);
            $aSizes = array(
                array(
                    'bytes'     => 1,
                    'converted' => array(
                        '1 b',
                        '1 b',
                        '1 b',
                        '1 b',
                        '1 b',
                        '1 b'
                    )
                ),
                array(
                    'bytes'     => 10,
                    'converted' => array(
                        '10 b',
                        '10 b',
                        '10 b',
                        '10 b',
                        '10 b',
                        '10 b'
                    )
                ),
                array(
                    'bytes'     => 100,
                    'converted' => array(
                        '100 b',
                        '100 b',
                        '100 b',
                        '100 b',
                        '100 b',
                        '100 b'
                    )
                ),
                array(
                    'bytes'     => 1000,
                    'converted' => array(
                        '1000 b',
                        '1000 b',
                        '1000 b',
                        '1000 b',
                        '1000 b',
                        '1000 b'
                    )
                ),
                array(
                    'bytes'     => 1024,
                    'converted' => array(
                        '1 kb',
                        '1 kb',
                        '1 kb',
                        '1 kb',
                        '1 kb',
                        '1 kb'
                    )
                ),
                array(
                    'bytes'     => 10000,
                    'converted' => array(
                        '9.8 kb',
                        '9.77 kb',
                        '9.766 kb',
                        '9.7656 kb',
                        '9.76563 kb',
                        '9.765625 kb'
                    )
                ),
                array(
                    'bytes'     => 100000,
                    'converted' => array(
                        '97.7 kb',
                        '97.66 kb',
                        '97.656 kb',
                        '97.6563 kb',
                        '97.65625 kb',
                        '97.65625 kb'
                    )
                ),
                array(
                    'bytes'     => 0,
                    'converted' => array(
                        '0',
                        '0',
                        '0',
                        '0',
                        '0',
                        '0'
                    )
                ),
                array(
                    'bytes'     => 4928769100928,
                    'converted' => array(
                        '4.5 Tb',
                        '4.48 Tb',
                        '4.483 Tb',
                        '4.4827 Tb',
                        '4.48269 Tb',
                        '4.482689 Tb'
                    )
                )
            );

            foreach ($aSizes as &$aSize) {
                foreach ($aPrecisions as $i => &$iPrecision) {
                    $sExpected = $aSize['converted'][$i];
                    $sActual = $this->oFormatter->formatBytes($aSize['bytes'], $iPrecision);
                    $sMessage = 'Converting ' . $aSize['bytes']
                        . ' bytes on precision ' . $iPrecision
                        . ' did not equal ' . $sExpected;

                    $this->assertEquals($sExpected, $sActual, $sMessage);
                }
            }

            try {
                $this->oFormatter->formatBytes(-5);
            } catch (\InvalidArgumentException $e) {
                try {
                    $this->oFormatter->formatBytes(500, -1);
                } catch (\InvalidArgumentException $e) {
                    return;
                }
            }
            $this->fail("Expected exceptions have not occurred");
        }

        public function testInputToTime()
        {
            $this->assertEquals(0, $this->oFormatter->toTime(0));
            $this->assertEquals(3, $this->oFormatter->toTime(3));
            $this->assertEquals(1347487200, $this->oFormatter->toTime('Sept 13th 2012'));
            $this->assertEquals(1347746400, $this->oFormatter->toTime('Sept 13th 2012 + 3 day'));
            $this->assertEquals(
                $this->oFormatter->toTime('2012-09-13'),
                $this->oFormatter->toTime('Sept 13 2012')
            );

            $this->setExpectedException('InvalidArgumentException');
            $this->oFormatter->toTime('Sept 42nd 2012');
            $this->oFormatter->toTime('Sept 39 2012');
            $this->oFormatter->toTime('2011-02-29');
            $this->assertEquals(-5, $this->oFormatter->toTime(-5));

            $this->setExpectedException(false);
            $this->oFormatter->toTime('2012-02-29');
        }

        public function testFormatDates()
        {
            $aDates = array(
                array(1347487200, 'Sep 13 2012', '2012-09-13'),
                array(1347746400, 'Sep 13 2012 +3 day', '2012-09-13 +3 day'),
                array(1347746400, 'Sep 13 2012 +3 day', '2012-09-16'),
                array(0, 'Jan 01 1970', '1970-01-01')
            );

            foreach ($aDates as $aDate) {
                $this->assertEquals(
                    $this->oFormatter->toMysqlDate($aDate[0]),
                    $this->oFormatter->toMysqlDate($aDate[2])
                );
                $this->assertEquals(
                    $this->oFormatter->toReadableDate($aDate[0]),
                    $this->oFormatter->toReadableDate($aDate[1])
                );
                $this->assertEquals(
                    $this->oFormatter->toMysqlDate($aDate[0]),
                    $this->oFormatter->toMysqlDate($aDate[1])
                );
                $this->assertEquals(
                    $this->oFormatter->toReadableDate($aDate[0]),
                    $this->oFormatter->toReadableDate($aDate[2])
                );
            }

            $this->setExpectedException('InvalidArgumentException');
            $this->oFormatter->toReadableDate(-5);
            $this->oFormatter->toMysqlDate(-5);
        }
    }
