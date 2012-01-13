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
			self::assertTrue(true);
			self::assertTrue(false, 'Expected');
			self::assertTrue('foo', 'Expected');
			self::assertTrue(null, 'Expected');
		}

		public function test_false(){
			self::assertFalse(false);
			self::assertFalse(true, 'Expected');
			self::assertFalse('foo', 'Expected');
			self::assertFalse(null, 'Expected');
		}

		public function test_null(){
			self::assertNull(null);
			self::assertNull('', 'Expected');
			self::assertNull(true, 'Expected');
			self::assertNull(false, 'Expected');
		}

		public function test_not_null(){
			self::assertNotNull(true);
			self::assertNotNull('');
			self::assertNotNull(null, 'Expected');
			self::assertNotNull(false);
		}

		public function test_empty(){
			self::assertEmpty('');
			self::assertEmpty('foo', 'Expected');
			self::assertEmpty(array());
			self::assertEmpty(null);
			self::assertEmpty(true, 'Expected');
			self::assertEmpty(false);
		}

		public function test_not_empty(){
			self::assertNotEmpty('', 'Expected');
			self::assertNotEmpty('foo');
			self::assertNotEmpty(array(), 'Expected');
			self::assertNotEmpty(null, 'Expected');
			self::assertNotEmpty(true);
			self::assertNotEmpty(false, 'Expected');
		}

		public function test_type(){
			self::assertIsA(array(), 'array');
			self::assertIsA(true, 'boolean');
			self::assertIsA(null, 'string', 'Expected');
			self::assertIsA('foo', 'string');
		}

		public function test_not_type(){
			self::assertNotA(array(), 'array', 'Expected');
			self::assertNotA(true, 'boolean', 'Expected');
			self::assertNotA(null, 'string');
			self::assertNotA('foo', 'string', 'Expected');
		}

		public function test_equal(){
			self::assertEqual(1 + 1, '2');
			self::assertEqual(true, false, 'Expected');
			self::assertEqual(null, '');
			self::assertEqual(false, 1, 'Expected');
		}

		public function test_not_equal(){
			self::assertNotEqual(1 + 1, '2', 'Expected');
			self::assertNotEqual(true, false);
			self::assertNotEqual(null, '', 'Expected');
			self::assertNotEqual(false, 1);
		}

		public function test_within_margin(){
			self::assertWithinMargin(10, 2, 9);
			self::assertWithinMargin(10, 5, 2, 'Expected');
			self::assertWithinMargin(-4, 5, 10);
		}

		public function test_outside_margin(){
			self::assertOutsideMargin(10, 2, 9, 'Expected');
			self::assertOutsideMargin(10, 5, 2);
			self::assertOutsideMargin(-4, 5, 10, 'Expected');
		}

		public function test_identical(){
			self::assertIdentical(1 + 1, 2);
			self::assertIdentical(true, true);
			self::assertIdentical(null, '', 'Expected');
			self::assertIdentical(false, 0, 'Expected');
		}

		public function test_not_identical(){
			self::assertNotIdentical(1 + 1, 2, 'Expected');
			self::assertNotIdentical(true, true, 'Expected');
			self::assertNotIdentical(null, '');
			self::assertNotIdentical(false, 0);
		}

		public function test_reference(){
			$a = 'foo';
			$b = 'bar';
			$c =& $a;
			self::assertReference($a, $c);
			self::assertReference($a, $b, 'Expected');
			self::assertReference($a, $a);
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
			self::assertClone($a, $b, 'Expected');
			self::assertClone($b, $d);
			self::assertClone($d, $d, 'Expected');
			self::assertClone($x, $y);
			$y['foo'] = 'foobar';
			self::assertClone($x, $y, 'Expected');
		}

		public function test_pattern(){
			self::assertPattern('foo', '/foo/i');
			self::assertPattern('foo', '/bar/i', 'Expected');
		}

		public function test_no_pattern(){
			self::assertNoPattern('foo', '/foo/i', 'Expected');
			self::assertNoPattern('foo', '/bar/i');
		}

		public function test_exception(){
			self::assertException(function(){
				throw new \Exception('exception');
			});
			self::assertException(function(){
				return 'foobar';
			}, 'Expected');
		}

		public function test_handle_exception(){
			throw new \Exception('ran into error');
		}

	}