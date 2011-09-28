<?php namespace TFD;

	class Benchmark{
	
		private static $marks = array();
		
		public static function start($name){
			self::$marks[$name] = microtime(true);
		}
		
		public function check($name, $decimal = 4){
			if(array_key_exists($name, self::$marks)){
				return round(microtime(true) - self::$marks[$name], $decimal);
			}
			return 0.0;
		}
		
		public function memory(){
			return number_format(memory_get_usage() / 1024 / 1024, 2);
		}
		
		public function peak_memory(){
			return number_format(memory_get_peak_usage() / 1024 / 1024, 2);
		}
	
	}