<?php namespace TFD;

	class Test{

		public static function run($test){
			$class = 'Content\Tests\\'.$test;
			if(!class_exists($class)){
				throw new \Exception('Test does not exist');
			}
			$class = new $class;
			foreach(get_class_methods($class) as $method){
				if(preg_match('/^test/i', $method)){
					// need to be able to catch exceptions thrown by tests
					call_user_func(array($class, $method));
				}
			}
			$results = Results::get();
			return Core\Render::view(array('view' => 'test', 'dir' => 'error'))->set_options(array('name' => $test, 'results' => $results));
		}

		public function assertEqual($result, $expect){
			Results::add(($result === $expect));
		}

		public function __call($method, $args){
			// forward to TFD\Test\Assertion
			call_user_func_array(array('TFD\Test\Assertion', $method), $args);
		}

	}

	class Results{
		
		private static $results = array();

		public static function add($passed){
			$trace = debug_backtrace();
			self::$results[$trace[2]['function']][] = array(
				'test' => $trace[1]['function'],
				'file' => $trace[1]['file'],
				'line' => $trace[1]['line'],
				'result' => ($passed === true) ? 'passed' : 'failed',
			);
		}

		public static function get(){
			return self::$results;
		}
	}