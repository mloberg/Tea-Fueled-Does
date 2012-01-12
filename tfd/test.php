<?php namespace TFD;

	use TFD\Test\Results;

	class Test{

		private static function run_test($test, $show_passed = false){
			$class = 'Content\Tests\\'.$test;
			if(!class_exists($class)){
				throw new \Exception('Test does not exist');
			}
			$class = new $class;
			foreach(get_class_methods($class) as $method){
				if(preg_match('/^test/i', $method)){
					try{
						call_user_func(array($class, $method));
					}catch(\Exception $e){
						Results::exception($method, $e);
					}
				}
			}
			return Results::get();
		}

		public static function run($test, $show_passed = false){
			$results = self::run_test($test, $show_passed);
			$class = 'Content\Tests\\'.$test;
			$name = (defined($class.'::name')) ? $class::name : $test;
			return Core\Render::view(array('view' => 'test', 'dir' => 'error'))->set_options(array('name' => $name, 'results' => $results, 'show_passed' => $show_passed));
		}

		public static function cli($test, $show_passed = false){
			$results = self::run_test($test, $show_passed);
			return $results;
		}

		public function page($page, $options = array()){
			return new Test\Page($page, $options);
		}

		/**
		 * Assertions
		 */
		
		public function assertTrue($result, $message = null){
			if(is_null($message)) $message = sprintf("Value [%s] should be true", var_export($result, true));
			Results::add(($result === true), $message);
		}

		public function assertFalse($result, $message = null){
			if(is_null($message)) $message = sprintf("Value [%s] should be false", var_export($result, true));
			Results::add(($result === false), $message);
		}

		public function assertNull($result, $message = null){
			if(is_null($message)) $message = sprintf("Value [%s] should be NULL", var_export($result, true));
			Results::add(is_null($result), $message);
		}

		public function assertNotNull($result, $message = null){
			if(is_null($message)) $message = sprintf("Value [%s] should not be NULL", var_export($result, true));
			Results::add(!is_null($result), $message);
		}

		public function assertIsA($result, $type, $message = null){
			if(is_null($message)) $message = sprintf("Value [%s] should be a %s", var_export($result, true), $type);
			Results::add((gettype($result) === $type), $message);
		}

		public function assertNotA($result, $type, $message = null){
			if(is_null($message)) $message = sprintf("Value [%s] should not be a %s", var_export($result, true), $type);
			Results::add(!(gettype($result) === $type), $message);
		}

		public function assertEqual($result, $expect, $message = null){
			if(is_null($message)) $message = sprintf("Value [%s] should be equal to [%s]", var_export($result, true), var_export($expect, true));
			Results::add(($result == $expect), $message);
		}

		public function assertNotEqual($result, $expect, $message = null){
			if(is_null($message)) $message = sprintf("Value [%s] should not equal be [%s]", var_export($result, true), var_export($expect, true));
			Results::add(($result != $expect), $message);
		}

		public function assertWithinMargin($x, $y, $margin, $message = null){
			if(is_null($message)) $message = sprintf("Value [%s] should be within [%s] by [%s]", var_export($x, true), var_export($y, true), var_export($margin, true));
			Results::add((abs($x - $y) < $margin), $message);
		}

		public function assertOutsideMargin($x, $y, $margin, $message = null){
			if(is_null($message)) $message = sprintf("Value [%s] should be outside [%s] by [%s]", var_export($x, true), var_export($y, true), var_export($margin, true));
			Results::add(!(abs($x - $y) < $margin), $message);
		}

		public function assertIdentical($result, $expect, $message = null){
			if(is_null($message)) $message = sprintf("Value [%s] should be identical to [%s]", var_export($result, true), var_export($expect, true));
			Results::add(($result === $expect), $message);
		}

		public function assertNotIdentical($result, $expect, $message = null){
			if(is_null($message)) $message = sprintf("Value [%s] should not be identical to [%s]", var_export($result, true), var_export($expect, true));
			Results::add(($result !== $expect), $message);
		}

		public function assertReference(&$x, &$y, $message = null){
			if(is_null($message)) $message = sprintf("Value [%s] should reference [%s]", var_export($y, true), var_export($x, true));
			Results::add(self::is_reference($x, $y), $message);
		}

		public function assertClone(&$x, &$y, $message = null){
			if(is_null($message)) $message = sprintf("Value [%s] should be a clone of [%s]", var_export($y, true), var_export($x, true));
			Results::add(self::is_clone($x, $y), $message);
		}
		
		public function assertPattern($match, $pattern, $message = null){
			if(is_null($message)) $message = sprintf("Value [%s] does not match [%s]", var_export($match, true), var_export($pattern, true));
			Results::add(((boolean)preg_match($pattern, $match)), $message);
		}

		public function assertNoPattern($match, $pattern, $message = null){
			if(is_null($message)) $message = sprintf("Value [%s] matches [%s]", var_export($match, true), var_export($pattern, true));
			Results::add((!(boolean)preg_match($pattern, $match)), $message);
		}

		public function assertException($function, $message = null){
			if(is_null($message)) $message = sprintf("Function should have thrown exception");
			try{
				$function();
				Results::add(false, $message);
			}catch(\Exception $e){
				Results::add(true, $message);
			}
		}

		/**
		 * Reference checking
		 */
		
		private function is_reference(&$a, &$b){
			// if they aren't equal, they aren't references
			if($a !== $b) return false;

			if(is_array($a)){
				do {
					$key = uniqid('is_ref_', true);
				}while(array_key_exists($key, $a));
				// the data differs
				if(array_key_exists($key, $b)) return false;
				$data = uniqid('is_ref_data_', true);
				// set new array key
				$a[$key] =& $data;
				// if the key exists in b and the data matches, it's a reference
				if(array_key_exists($key, $b)){
					if($b[$key] === $data){
						// clear the data we added
						unset($a[$key]);
						return true;
					}
				}
				// clear the data we added
				unset($a[$key]);
				return false;
			}elseif(is_object($a)){
				// if not of the same class, not a reference
				if(get_class($a) !== get_class($b)) return false;
				$obj1 = array_keys(get_object_vars($a));
				$obj2 = array_keys(get_object_vars($b));
				do{
					$key = uniqid('is_ref_', true);
				}while(in_array($key, $obj1));
				// the data differs
				if(in_array($key, $obj2)) return false;
				$data = uniqid('is_ref_data_', true);
				$a->$key =& $data;
				// if the key exists in b and the data matches, it's a reference
				if(isset($b->$key)){
					if($b[$key] === $data){
						unset($a->$key);
						return true;
					}
				}
				unset($a->$key);
				return false;
			}elseif(is_resource($a)){
				if(get_resource_type($a) !== get_resource_type($b)) return false;
				return ((string)$a === (string)$b);
			}else{
				do{
					$key = uniqid('is_ref_', true);
				}while($key === $a);

				$tmp = $a;
				$a = $key;
				if($a === $b){
					$a = $tmp;
					return true;
				}
				$a = $tmp;
				return false;
			}
			return false;
		}

		private function is_clone(&$a, &$b){
			if($a !== $b) return false;
			if(self::is_reference($a, $b)) return false;
			return true;
		}

	}