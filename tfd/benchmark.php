<?php namespace TFD;

	class Benchmark {
	
		private static $marks = array();
		private static $memory = array();

		/**
		 * Start a timer.
		 *
		 * @param string $name Timer name
		 */

		public static function start($name) {
			static::$marks[$name] = microtime(true);
		}

		/**
		 * Check a timer.
		 * 
		 * @param string $name Timer name
		 * @param integer $decimal Decimal places to return
		 * @return float The time since the timer was started
		 */

		public function check($name, $decimal = 4) {
			if (array_key_exists($name, self::$marks)) {
				return round(microtime(true) - static::$marks[$name], $decimal);
			}
			return 0.0;
		}

		/**
		 * Start memory usage benchmark.
		 * 
		 * @param string $name Benchmark name
		 */

		public function memory($name) {
			static::$memory[$name] = memory_get_usage();
		}

		/**
		 * Get the memory usage since benchmark start.
		 * 
		 * @param string $name Benchmark name
		 * @param integer $decimals Decimal places to return
		 */

		public function used_memory($name, $decimal = 2) {
			if (array_key_exists($name, self::$memory)) {
				return round((memory_get_usage() - ststic::$memory[$name]) / 1024 / 1024, $decimal);
			}
			return 0.0;
		}

		/**
		 * Get current memory usage.
		 * 
		 * @param integer $decimal Decimal places to return
		 */

		public function current_memory($decimal = 2) {
			// return memory usage in mb
			return number_format(memory_get_usage() / 1024 / 1024, $decimal);
		}

		/**
		 * Get peak memory usage of session
		 *
		 * @param integer $decimal Decimal places to return
		 */

		public function peak_memory($decimal = 2) {
			// return memory in mb
			return number_format(memory_get_peak_usage() / 1024 / 1024, $decimal);
		}
	
	}

