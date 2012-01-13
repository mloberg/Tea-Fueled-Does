<?php namespace TFD\Test;

	class Results{
		
		private static $results = array();

		public static function add($passed, $message){
			$trace = debug_backtrace();
			self::$results[$trace[2]['function']][] = array(
				'test' => $trace[1]['function'],
				'file' => $trace[1]['file'],
				'line' => $trace[1]['line'],
				'result' => ($passed === true) ? 'passed' : 'failed',
				'message' => $message,
			);
		}

		public static function exception($method, $e){
			self::$results[$method][] = array(
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'result' => 'exception',
				'message' => $e->getMessage(),
			);
		}

		public static function get(){
			$results = self::$results;
			self::$results = array();
			return $results;
		}
		
	}