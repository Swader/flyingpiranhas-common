<?php

    namespace flyingpiranhas\common\utils;

    use flyingpiranhas\common\utils\Format;
    use flyingpiranhas\common\exceptions\FpException;

    /**
     * Stopwatch is a static benchmarking class for easy measuring of
     * execution time and memory usage through checkpoints in code
     *
     * This class was made singleton because its only purpose is debugging
     * and because we wanted it to be accessible across classes and objects
     * without having to repeatedly inject it everywhere
     *
     * This enables you to measure performance and memory across a wide array
     * of classes, controllers, actions and everything else without much
     * difficulty.
     *
     * @category       Utilities
     * @package        flyingpiranhas.common
     * @license        BSD License
     * @version        0.01
     * @since          2012-09-07
     * @author         Bruno Škvorc <bruno@skvorc.me>
     */
    class Stopwatch
    {

        /**
         * Last checkpoint's memory cost
         * @var int
         */
        protected static $iLastMem;

        /**
         * Last checkpoint's duration
         * @var int
         */
        protected static $iLastTime;

        /**
         * Last checkpoint's real memory cost
         * @var
         */
        protected static $iLastRealMem;

        /**
         * An array of checkpoints
         * @var array
         */
        protected static $aCheckpoints;

        /**
         * Starts the stopwatch
         *
         * @return void
         */
        public static function start()
        {
            self::reset();

            self::$iLastMem = 0;
            self::$iLastRealMem = 0;
            self::$iLastTime = 0;

            if (floatval(phpversion()) >= 5.4) {
                self::checkpoint('request_time_float');
            }
            self::checkpoint('start');
        }

        /**
         * Returns the first checkpoint. If the PHP version is 5.4+, the
         * first checkpoint will be a "request_time_float" checkpoint, otherwise
         * it will be the "start" checkpoint.
         *
         * @return array
         */
        public static function getFirstCheckpoint()
        {
            if (!self::isRunning()) {
                self::start();
            }
            foreach (self::$aCheckpoints as &$aCheckpoint) {
                return $aCheckpoint;
            }
            return null;
        }

        /**
         * Reinitializes the stopwatch component
         *
         * @return void
         */
        public static function reset()
        {
            self::$aCheckpoints = array();
        }

        /**
         * Sets a new checkpoint with the given label.
         * If no label is given, the checkpoint is labeled as the current
         * number of checkpoints minus one
         *
         * @param string $sLabel
         *
         * @return string|int
         */
        public static function checkpoint($sLabel = null)
        {
            if ($sLabel != 'request_time_float') {
                if (!self::isRunning() && $sLabel != 'start') {
                    self::start();
                }
            }

            $sLabel = ($sLabel) ? $sLabel : count(self::$aCheckpoints) - 1;

            $iTime = ($sLabel == 'request') ? $_SERVER['REQUEST_TIME_FLOAT'] : microtime(true);
            $iMemoryPeak = memory_get_peak_usage();
            $iMemoryPeakReal = memory_get_peak_usage(true);

            self::$aCheckpoints[$sLabel] = array(
                'label'               => $sLabel,
                'time'                => $iTime,
                'lasted'              => (self::$iLastTime) ? number_format($iTime - self::$iLastTime, 4) : 0,
                'peakMemory'          => $iMemoryPeak,
                'increasedMemory'     => ((self::$iLastMem)) ? $iMemoryPeak - self::$iLastMem : 0,
                'peakMemoryReal'      => $iMemoryPeakReal,
                'increasedMemoryReal' => ((self::$iLastRealMem)) ? $iMemoryPeakReal - self::$iLastRealMem : 0,
            );

            self::$iLastMem = $iMemoryPeak;
            self::$iLastRealMem = $iMemoryPeakReal;
            self::$iLastTime = $iTime;

            return $sLabel;
        }

        /**
         * Checks if the Stopwatch is currently running
         *
         * @return bool
         */
        public static function isRunning()
        {
            return (isset(self::$aCheckpoints['start']));
        }

        /**
         * Processes the checkpoints to get some readable values
         * The processed formatted values are saved within the
         * checkpoints themselves
         *
         * @return void
         * @throws StopwatchException
         */
        protected static function processResult()
        {
            if (!self::isRunning()) {
                throw new StopwatchException('Stopwatch is not running. No checkpoints to process.');
            }

            foreach (self::$aCheckpoints as &$aFirstCheckpoint) {
                $aFirstCheckpoint = self::getFirstCheckpoint();

                $aFirstCheckpoint['to'] = $aFirstCheckpoint['label'];
                $aFirstCheckpoint['from'] = ($aFirstCheckpoint['label'] == 'start') ? "Stopwatch start" : "Application start";
                $aFirstCheckpoint['totalTimeUntil'] = 0;
                $aFirstCheckpoint['peakMemoryReadable'] = Format::formatBytes(
                    $aFirstCheckpoint['peakMemory'],
                    2
                );
                $aFirstCheckpoint['peakMemoryRealReadable'] = Format::formatBytes(
                    $aFirstCheckpoint['peakMemoryReal'],
                    2
                );
                $aFirstCheckpoint['increasedMemoryReadable'] = 0;
                $aFirstCheckpoint['increasedMemoryRealReadable'] = 0;

                $sLastKey = $aFirstCheckpoint['label'];
                break;
            }

            $sLastKey = (!isset($sLastKey)) ? 'start' : $sLastKey;

            foreach (self::$aCheckpoints as $key => &$value) {
                if ($key != 'request_time_float' && (($key == 'start' && $sLastKey != 'start') || $key != 'start')) {
                    $value['to'] = $key;
                    $value['from'] = $sLastKey;
                    $value['totalTimeUntil'] = number_format($value['time'] - self::$aCheckpoints['start']['time'], 4);
                    $value['peakMemoryReadable'] = Format::formatBytes($value['peakMemory'], 2);
                    $value['peakMemoryRealReadable'] = Format::formatBytes($value['peakMemoryReal'], 2);
                    $value['increasedMemoryReadable'] = Format::formatBytes($value['increasedMemory'], 2);
                    $value['increasedMemoryRealReadable'] = Format::formatBytes($value['increasedMemoryReal'], 2);
                }
                $sLastKey = $key;
            }
        }

        /**
         * Processes the checkpoints into readable values and
         * returns the array.
         *
         * @return array
         */
        public static function getResult()
        {
            self::processResult();
            return self::$aCheckpoints;
        }

        /**
         * Returns the results for the specified checkpoint only.
         * If no label is specified, returns the stats for the last one.
         *
         * @param string $label
         *
         * @return array
         * @throws StopwatchException
         */
        public static function getCheckpointResults($label = null)
        {
            self::processResult();

            if (!$label) {
                return end(self::$aCheckpoints);
            } elseif (isset(self::$aCheckpoints[$label])) {
                return self::$aCheckpoints[$label];
            } else {
                throw new StopwatchException("This checkpoint ({$label}) does not exist in the stopwatch object!");
            }
        }

        /**
         * Returns a float on the duration of the checkpoint down to the
         * microsecond (e.g. 0.0005)
         *
         * @param string $label Checkpoint label
         *
         * @return float
         */
        public static function getCheckpointDuration($label = null)
        {
            $checkpoint = self::getCheckpointResults($label);
            return floatval($checkpoint['lasted']);
        }

        /**
         * Returns the actual memory cost of the checkpoint.
         * This is how much the memory expense rose during the checkpoint
         *
         * @param string $label
         * @param bool   $readable Whether or not to turn it into human
         *                         readable format (as opposed to pure byte value)
         *
         * @return mixed
         */
        public static function getMemoryCost($label = null, $readable = true)
        {
            $checkpoint = self::getCheckpointResults($label);
            return ($readable) ? $checkpoint['increasedMemoryReadable'] : $checkpoint['increasedMemory'];
        }

        /**
         * Returns the actual REAL memory cost of the checkpoint.
         * This is how much the REAL memory expense rose during the checkpoint
         *
         * @param string $label
         * @param bool   $readable Whether or not to turn it into human
         *                         readable format (as opposed to pure byte value)
         *
         * @return mixed
         */
        public static function getMemoryCostReal($label = null, $readable = true)
        {
            $checkpoint = self::getCheckpointResults($label);
            return ($readable) ? $checkpoint['increasedMemoryRealReadable'] : $checkpoint['increasedMemoryReal'];
        }

        /**
         * Returns the time it took to reach the point of the given checkpoint,
         * or until the last checkpoint if no checkpoint is given.
         * Data is returned as float - seconds with microseconds
         *
         * (useful for "This search took X seconds" messages to visitors)
         *
         * @param string $label
         *
         * @return float
         */
        public static function getDurationUntil($label = null)
        {
            $checkpoint = self::getCheckpointResults($label);
            return floatval($checkpoint['totalTimeUntil']);
        }

        /**
         * Returns the maximum amount of memory the request spent at
         * any given moment.
         *
         * @param bool $real [Optional] Set to true to get the REAL memory.
         *                   Refer to memory_get_peak_usage documentation
         *                   for details on $real
         *
         * @return string
         */
        public static function getReadableMemoryPeak($real = false)
        {
            $checkpoint = self::getCheckpointResults();
            return ($real) ? $checkpoint['peakMemoryRealReadable'] : $checkpoint['peakMemoryReadable'];
        }

        /**
         * Returns the names of all up until now defined checkpoints
         *
         * @return array
         */
        public static function getCheckpointLabels()
        {
            return array_keys(self::$aCheckpoints);
        }

        /**
         * Renders a tabled output of results
         * @return string
         */
        public static function renderTable()
        {
            $aResults = self::getResult();
            ob_start();
            ?>
        <table class="fpStopwatchResult">
            <thead>
            <tr>
                <th>#</th>
                <th>From</th>
                <th>To</th>
                <th>Lasted</th>
                <th>Total time until</th>
                <th>Peak Memory (real)</th>
                <th>Increased Memory (real)</th>
            </tr>
            </thead>
            <tbody>
                <?php $i = 0; foreach ($aResults as &$aCheckpoint) : $i++ ?>
            <tr>
                <td><?php echo $i; ?></td>
                <td><?php echo $aCheckpoint['from'] ?></td>
                <td><?php echo $aCheckpoint['to'] ?></td>
                <td><?php echo $aCheckpoint['lasted']; ?></td>
                <td><?php echo $aCheckpoint['totalTimeUntil']; ?></td>
                <td><?php echo $aCheckpoint['peakMemoryReadable'] . '(' . $aCheckpoint['peakMemoryRealReadable'] . ')'; ?></td>
                <td><?php echo $aCheckpoint['increasedMemoryReadable'] . '(' . $aCheckpoint['increasedMemoryRealReadable'] . ')'; ?></td>
            </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
            return ob_get_clean();
        }
    }

    /**
     * The Stopwatch exception can only occur while using the
     * Stopwatch component
     *
     * @category       Flyingpiranhas
     * @package        exceptions
     * @license        BSD License
     * @version        alpha
     * @since          2012-09-07
     * @author         Bruno Škvorc <bruno@skvorc.me>
     */
    class StopwatchException extends FpException
    {

    }
