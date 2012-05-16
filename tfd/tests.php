<?php namespace TFD;

	use TFD\Tests\Results;

	class Tests {

		private $results;
		private $class;

		public function __construct($test) {
			$class = 'Content\Tests\\'.$test;
			if (!class_exists($class)) {
				throw new \Exception("Test {$test} does not exist");
			}
			$name = (defined($class.'::name')) ? $class::name : $test;
			$this->class = new $class;
			$this->results = new Results;
		}

		public function run_test() {
			// 
		}

		/**
		 *
		 */

		private static function run_test($test) {
			$class = 'Content\Tests\\'.$test;
			if (!class_exists($class)) {
				throw new \Exception("Test {$test} does not exist");
			}
			$name = (defined($class.'::name')) ? $class::name : $test;
			$class = new $class;
			$results = new Results;
			foreach (get_class_methods($class) as $method) {
				if (preg_match('/^test/i', $method)) {
					try {
						call_user_func(array($class, $method));
					} catch (\Exception $e) {
						// 
					}
				}
			}
		}

		/**
		 * Run a test.
		 * 
		 * @param string $test Test to run
		 */

		public static function run($test) {
			// 
		}

		/**
		 *
		 */

		public static function __callStatic($method, $args) {
			if (preg_match('/^assert/', $method)) {
				// 
			}
		}

	}
