<?php namespace TFD\Test;

	class Assertion{

		public function assertTrue($result){
			// 
			print_p(debug_backtrace());
		}

		public function assertFalse($result){
			//
		}

		public function assertEqual($result, $equal){
			//
			print_p(self::backtrace());
		}

		private function backtrace(){
			$trace = debug_backtrace();
			$test = $trace[5];
			$call = $trace[4];
			return $trace;
		}

	}

	/**
	 * Assertions should be collected 