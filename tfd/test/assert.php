<?php namespace TFD\Test;

	class Assert{
		
		public function true($result, $message = null){
			Results::add(($result === true), $message);
		}

		public function false($result, $message = null){
			Results::add(($result === false), $message);
		}

		public function null($result, $message = null){
			Results::add(is_null($result), $message);
		}

		public function notNull($result, $message = null){
			Results::add(!is_null($result), $message);
		}

		public function isEmpty($result, $message = null){
			Results::add(empty($result), $message);
		}

		public function notEmpty($result, $message = null){
			Results::add(!empty($result), $message);
		}

		public function type($result, $type, $message = null){
			Results::add((gettype($result) === $type), $message);
		}

		public function notType($result, $type, $message = null){
			Results::add(!(gettype($result) === $type), $message);
		}

		public function isA($result, $type, $message = null){
			Results::add(is_a($result, $type), $message);
		}

		public function notA($result, $type, $message = null){
			Results::add(!is_a($result, $type), $message);
		}

		public function equal($result, $expect, $message = null){
			Results::add(($result == $expect), $message);
		}

		public function notEqual($result, $expect, $message = null){
			Results::add(($result != $expect), $message);
		}

		public function withinMargin($x, $y, $margin, $message = null){
			Results::add((abs($x - $y) < $margin), $message);
		}

		public function outsideMargin($x, $y, $margin, $message = null){
			Results::add(!(abs($x - $y) < $margin), $message);
		}

		public function identical($result, $expect, $message = null){
			Results::add(($result === $expect), $message);
		}

		public function notIdentical($result, $expect, $message = null){
			Results::add(($result !== $expect), $message);
		}

		public function reference(&$x, &$y, $message = null){
			Results::add(self::is_reference($x, $y), $message);
		}

		public function isClone(&$x, &$y, $message = null){
			Results::add(self::is_clone($x, $y), $message);
		}

		public function pattern($match, $pattern, $message = null){
			Results::add(((boolean)preg_match($pattern, $match)), $message);
		}

		public function notPattern($match, $pattern, $message = null){
			Results::add((!(boolean)preg_match($pattern, $match)), $message);
		}

		public function exception($function, $message = null){
			try{
				$function();
				Results::add(false, $message);
			}catch(\Exception $e){
				Results::add(true, $message);
			}
		}

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