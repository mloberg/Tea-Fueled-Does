<?php namespace TFD;

	class Benchmark{
	
		private static $marks = array();
		private static $memory = array();
		
		public static function start($name){
			self::$marks[$name] = microtime(true);
		}
		
		public function check($name, $decimal = 4){
			if(array_key_exists($name, self::$marks)){
				return round(microtime(true) - self::$marks[$name], $decimal);
			}
			return 0.0;
		}
		
		public function memory($name){
			self::$memory[$name] = memory_get_usage();
		}
		
		public function used_memory($name, $decimal = 2){
			if(array_key_exists($name, self::$memory)){
				return round((memory_get_usage() - self::$memory[$name]) / 1024 / 1024, $decimal);
			}
			return 0.0;
		}
		
		public function current_memory($decimal = 2){
			// return memory usage in mb
			return number_format(memory_get_usage() / 1024 / 1024, $decimal);
		}
		
		public function peak_memory($decimal = 2){
			// return memory in mb
			return number_format(memory_get_peak_usage() / 1024 / 1024, $decimal);
		}
	
	}