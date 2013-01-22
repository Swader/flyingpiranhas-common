<?php

    namespace flyingpiranhas\common\tests\utils;

    use flyingpiranhas\common\utils\Stopwatch;

    /**
     * Class StopwatchTest.php
     *
     * @category      Tests
     * @package       Utils
     * @license       BSD License
     * @version       0.01
     * @since         2012-10-07
     * @author        Bruno Škvorc <bruno@skvorc.me>
     */
    class StopwatchTest extends \PHPUnit_Framework_TestCase
    {

        public function testStarting()
        {
            $this->initBogus();
            $aResult = Stopwatch::getResult();
            if ($this->is54()) {
                $this->assertArrayHasKey('request_time_float', $aResult);
            }
            $this->assertArrayHasKey('start', $aResult);
            $this->assertTrue(Stopwatch::isRunning());
        }

        public function testGetFirstCheckpoint()
        {
            $aFirstCheckpoint = Stopwatch::getFirstCheckpoint();
            if ($this->is54()) {
                $this->assertEquals('request_time_float', $aFirstCheckpoint['label']);
            } else {
                $this->assertEquals('start', $aFirstCheckpoint['label']);
            }
            $this->assertArrayHasKey('peakMemoryReadable', $aFirstCheckpoint);
            $this->assertArrayHasKey('peakMemoryRealReadable', $aFirstCheckpoint);
            $this->assertArrayHasKey('increasedMemoryReadable', $aFirstCheckpoint);
            $this->assertArrayHasKey('increasedMemoryRealReadable', $aFirstCheckpoint);
        }

        public function testResultFormatOk()
        {
            $this->initBogus();
            foreach (Stopwatch::getResult() as $aCheckpoint) {
                $aKeys = array(
                    'to',
                    'from',
                    'totalTimeUntil',
                    'peakMemoryReadable',
                    'peakMemoryRealReadable',
                    'increasedMemoryReadable',
                    'increasedMemoryRealReadable',
                    'label',
                    'time',
                    'lasted',
                    'peakMemory',
                    'increasedMemory',
                    'peakMemoryReal',
                    'increasedMemoryReal'
                );
                foreach ($aKeys as &$sKey) {
                    $this->assertArrayHasKey($sKey, $aCheckpoint);
                }
            }
        }

        public function testCheckpointAttributes()
        {
            $this->initBogus();
            $this->assertTrue(is_float(Stopwatch::getCheckpointDuration()));
            $this->assertTrue(is_numeric(Stopwatch::getMemoryCost(null, false)));
            $this->assertTrue(is_string(Stopwatch::getMemoryCost(null, true)) || Stopwatch::getMemoryCost(null, true) == 0);
            $this->assertTrue(is_numeric(Stopwatch::getMemoryCostReal(null, false)));
            $this->assertTrue(is_string(Stopwatch::getMemoryCostReal(null, true)) || Stopwatch::getMemoryCostReal(null, true) == 0);
            $this->assertTrue(is_float(Stopwatch::getDurationUntil()) || Stopwatch::getDurationUntil() == 0);
            $this->assertTrue(is_string(Stopwatch::getReadableMemoryPeak()) || Stopwatch::getReadableMemoryPeak() == 0);
        }

        public function testGetLabels()
        {
            $this->initBogus();
            if ($this->is54()) {
                $aExpectedKeys = array('request_time_float', 'start', 'someCheck', 'anotherCheck', 3, 4);
            } else {
                $aExpectedKeys = array('start', 'someCheck', 'anotherCheck', 2, 3);
            }
            foreach ($aExpectedKeys as &$sKey) {
                $this->assertContains($sKey, Stopwatch::getCheckpointLabels());
            }
        }

        public function testReset()
        {
            $this->initBogus();
            $this->assertNotEmpty(Stopwatch::getResult());
            $this->assertTrue(Stopwatch::isRunning());
            Stopwatch::reset();
            $this->assertFalse(Stopwatch::isRunning());

            $this->setExpectedException('\flyingpiranhas\common\utils\StopwatchException');
            Stopwatch::getResult();
            Stopwatch::getCheckpointDuration();
        }

        public function testExceptionOnNotRunning()
        {
            $this->setExpectedException('\flyingpiranhas\common\utils\StopwatchException');
            Stopwatch::getResult();
        }

        public function testCheckpoint()
        {
            $sLabel = Stopwatch::checkpoint('testCheckpoint');
            $aResults = Stopwatch::getResult();
            $this->assertArrayHasKey($sLabel, $aResults);
            $this->assertArrayHasKey('testCheckpoint', $aResults);
            $aCheckpoint = Stopwatch::getCheckpointResults($sLabel);
            $aKeys = array(
                'to',
                'from',
                'totalTimeUntil',
                'peakMemoryReadable',
                'peakMemoryRealReadable',
                'increasedMemoryReadable',
                'increasedMemoryRealReadable',
                'label',
                'time',
                'lasted',
                'peakMemory',
                'increasedMemory',
                'peakMemoryReal',
                'increasedMemoryReal'
            );
            foreach ($aKeys as &$sKey) {
                $this->assertArrayHasKey($sKey, $aCheckpoint);
            }

            $sLabel = Stopwatch::checkpoint();
            $aResults = Stopwatch::getResult();
            $this->assertArrayHasKey($sLabel, $aResults);
            $aCheckpoint = Stopwatch::getCheckpointResults($sLabel);
            $aKeys = array(
                'to',
                'from',
                'totalTimeUntil',
                'peakMemoryReadable',
                'peakMemoryRealReadable',
                'increasedMemoryReadable',
                'increasedMemoryRealReadable',
                'label',
                'time',
                'lasted',
                'peakMemory',
                'increasedMemory',
                'peakMemoryReal',
                'increasedMemoryReal'
            );
            foreach ($aKeys as &$sKey) {
                $this->assertArrayHasKey($sKey, $aCheckpoint);
            }
        }

        protected function initBogus()
        {
            Stopwatch::start();
            Stopwatch::checkpoint('someCheck');
            sleep(1);
            Stopwatch::checkpoint('anotherCheck');
            Stopwatch::checkpoint();
            sleep(1);
            Stopwatch::checkpoint();
        }

        /**
         * Checks if the PHP version is at or above 5.4
         * @return bool
         */
        protected function is54()
        {
            return floatval(phpversion()) >= 5.4;
        }
    }
