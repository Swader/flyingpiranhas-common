<?php

	namespace flyingpiranhas\common\utils;

	/**
	 * Contains some helper methods for debugging your code and
	 * for prettier output of dumper variables. It also allows you to
	 * returns some complex variables like arrays in the form of
	 * strings, in case you're, for example, sending a report to the
	 * developer, or outputting the desired variable in an exception message.
	 *
	 * @category       Utilities
	 * @package        flyingpiranhas.common
	 * @license        BSD License
	 * @version        0.01
	 * @since          2012-09-07
	 * @author         Bruno Škvorc <bruno@skvorc.me>
	 */
	class Debug
	{

		/**
		 * This method helps debugging by providing a simple shorthand solution
		 * for outputting complex variables (like nested arrays) in a human
		 * readable manner. This is helpful when a variable of unknown type
		 * needs to be appended to an exception message, and similar scenarios
		 *
		 * @param mixed $mVar The variable to dump
		 * @param bool  $bPre Whether or not to wrap the output in <pre> tags
		 * @param bool  $bDie Whether to die the output or return it
		 *
		 * @return string
		 */
		public static function vd($mVar, $bPre = false, $bDie = false)
		{

			ob_start();
			var_dump($mVar);
			$mVar = ob_get_clean();

			if ($bPre) {
				$mVar = '<pre>' . $mVar . '</pre>';
			}

			if ($bDie) {
				die($mVar);
			} else {
				return $mVar;
			}
		}

		/**
		 * @see self::vd()
		 *
		 * @param mixed $mVar
		 * @param bool  $bPre
		 */
		public static function vdd($mVar, $bPre = false)
		{
			self::vd($mVar, $bPre, 1);
		}

		/**
		 * @see self::vd()
		 *
		 * @param mixed $mVar
		 * @param bool  $bDie
		 *
		 * @return string
		 */
		public static function vdp($mVar, $bDie = false)
		{
			return self::vd($mVar, 1, $bDie);
		}

		/**
		 * @see self::vd()
		 *
		 * @param mixed $mVar
		 */
		public static function vddp($mVar)
		{
			self::vd($mVar, 1, 1);
		}
	}
