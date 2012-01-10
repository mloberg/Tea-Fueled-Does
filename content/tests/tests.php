<?php namespace Content\Tests;

	use TFD\Test;

	/**
	 * Tests for the TFD\Test.
	 * Some of these tests are written so that they fail.
	 * In fact, if this passes, there is something wrong.
	 */

	class Tests extends Test{
		
		const name = 'TFD\Test';

		public function test_true(){
			$this->assertTrue(true);
			$this->assertTrue(false, 'Expected');
			$this->assertTrue('foo', 'Expected');
			$this->assertTrue(null, 'Expected');
		}

		public function test_false(){
			$this->assertFalse(false);
			$this->assertFalse(true, 'Expected');
			$this->assertFalse('foo', 'Expected');
			$this->assertFalse(null, 'Expected');
		}

		public function test_null(){
			$this->assertNull(null);
			$this->assertNull('', 'Expected');
			$this->assertNull(true, 'Expected');
			$this->assertNull(false, 'Expected');
		}

		public function test_not_null(){
			$this->assertNotNull(true);
			$this->assertNotNull('');
			$this->assertNotNull(null, 'Expected');
			$this->assertNotNull(false);
		}

		public function test_type(){
			$this->assertIsA(array(), 'array');
			$this->assertIsA(true, 'boolean');
			$this->assertIsA(null, 'string', 'Expected');
			$this->assertIsA('foo', 'string');
		}

		public function test_not_type(){
			$this->assertNotA(array(), 'array', 'Expected');
			$this->assertNotA(true, 'boolean', 'Expected');
			$this->assertNotA(null, 'string');
			$this->assertNotA('foo', 'string', 'Expected');
		}

		public function test_equal(){
			$this->assertEqual(1 + 1, '2');
			$this->assertEqual(true, false, 'Expected');
			$this->assertEqual(null, '');
			$this->assertEqual(false, 1, 'Expected');
		}

		public function test_not_equal(){
			$this->assertNotEqual(1 + 1, '2', 'Expected');
			$this->assertNotEqual(true, false);
			$this->assertNotEqual(null, '', 'Expected');
			$this->assertNotEqual(false, 1);
		}

		public function test_within_margin(){
			$this->assertWithinMargin(10, 2, 9);
			$this->assertWithinMargin(10, 5, 2, 'Expected');
			$this->assertWithinMargin(-4, 5, 10);
		}

		public function test_outside_margin(){
			$this->assertOutsideMargin(10, 2, 9, 'Expected');
			$this->assertOutsideMargin(10, 5, 2);
			$this->assertOutsideMargin(-4, 5, 10, 'Expected');
		}

		public function test_identical(){
			$this->assertIdentical(1 + 1, 2);
			$this->assertIdentical(true, true);
			$this->assertIdentical(null, '', 'Expected');
			$this->assertIdentical(false, 0, 'Expected');
		}

		public function test_not_identical(){
			$this->assertNotIdentical(1 + 1, 2, 'Expected');
			$this->assertNotIdentical(true, true, 'Expected');
			$this->assertNotIdentical(null, '');
			$this->assertNotIdentical(false, 0);
		}

		public function test_reference(){
			$a = 'foo';
			$b = 'bar';
			$c =& $a;
			$this->assertReference($a, $c);
			$this->assertReference($a, $b, 'Expected');
			$this->assertReference($a, $a);
		}

		public function test_clone(){
			$a = 'foo';
			$b = 'bar';
			$c =& $a;
			$d = $b;
			$x = array(
				'foo' => 'bar',
				'hello' => 'world'
			);
			$y = $x;
			$this->assertClone($a, $b, 'Expected');
			$this->assertClone($b, $d);
			$this->assertClone($d, $d, 'Expected');
			$this->assertClone($x, $y);
			$y['foo'] = 'foobar';
			$this->assertClone($x, $y, 'Expected');
		}

		public function test_pattern(){
			$this->assertPattern('foo', '/foo/i');
			$this->assertPattern('foo', '/bar/i', 'Expected');
		}

		public function test_no_pattern(){
			$this->assertNoPattern('foo', '/foo/i', 'Expected');
			$this->assertNoPattern('foo', '/bar/i');
		}

		public function test_exception(){
			$this->assertException(function(){
				throw new \Exception('exception');
			});
			$this->assertException(function(){
				return 'foobar';
			}, 'Expected');
		}

		public function test_handle_exception(){
			throw new \Exception('ran into error');
		}

	}