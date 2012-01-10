<?php namespace Content\Tests;

	use TFD\Test;

	class Math extends Test{
		
		public function test_one_and_one_makes_two(){
			$this->assertEqual(1 + 1, 2);
			$this->assertEqual(1 + 2, 2);
		}

		public function test_foo_bar(){
			$this->assertEqual('foo', 'foo');
		}

	}