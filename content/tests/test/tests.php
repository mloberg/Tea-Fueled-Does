<?php namespace Content\Tests\Test;

	use TFD\Test;

	/**
	 * Tests for the TFD\Test.
	 * Some of these tests are written so that they fail.
	 * In fact, if this passes, there is something wrong.
	 */

	class Tests extends Test{
		
		const name = 'TFD\Test';

		public function test_true(){
			Test\Assert::true(true);
			Test\Assert::true(false, 'Expected');
			Test\Assert::true('foo', 'Expected');
			Test\Assert::true(null, 'Expected');
		}

		public function test_false(){
			Test\Assert::false(false);
			Test\Assert::false(true, 'Expected');
			Test\Assert::false('foo', 'Expected');
			Test\Assert::false(null, 'Expected');
		}

		public function test_null(){
			Test\Assert::null(null);
			Test\Assert::null('', 'Expected');
			Test\Assert::null(true, 'Expected');
			Test\Assert::null(false, 'Expected');
		}

		public function test_not_null(){
			Test\Assert::notNull(true);
			Test\Assert::notNull('');
			Test\Assert::notNull(null, 'Expected');
			Test\Assert::notNull(false);
		}

		public function test_empty(){
			Test\Assert::isEmpty('');
			Test\Assert::isEmpty('foo', 'Expected');
			Test\Assert::isEmpty(array());
			Test\Assert::isEmpty(null);
			Test\Assert::isEmpty(true, 'Expected');
			Test\Assert::isEmpty(false);
		}

		public function test_not_empty(){
			Test\Assert::notEmpty('', 'Expected');
			Test\Assert::notEmpty('foo');
			Test\Assert::notEmpty(array(), 'Expected');
			Test\Assert::notEmpty(null, 'Expected');
			Test\Assert::notEmpty(true);
			Test\Assert::notEmpty(false, 'Expected');
		}

		public function test_type(){
			Test\Assert::type(array(), 'array');
			Test\Assert::type(true, 'boolean');
			Test\Assert::type(null, 'string', 'Expected');
			Test\Assert::type('foo', 'string');
		}

		public function test_not_type(){
			Test\Assert::notType(array(), 'array', 'Expected');
			Test\Assert::notType(true, 'boolean', 'Expected');
			Test\Assert::notType(null, 'string');
			Test\Assert::notType('foo', 'string', 'Expected');
		}

		public function test_equal(){
			Test\Assert::equal(1 + 1, '2');
			Test\Assert::equal(true, false, 'Expected');
			Test\Assert::equal(null, '');
			Test\Assert::equal(false, 1, 'Expected');
		}

		public function test_not_equal(){
			Test\Assert::notEqual(1 + 1, '2', 'Expected');
			Test\Assert::notEqual(true, false);
			Test\Assert::notEqual(null, '', 'Expected');
			Test\Assert::notEqual(false, 1);
		}

		public function test_within_margin(){
			Test\Assert::withinMargin(10, 2, 9);
			Test\Assert::withinMargin(10, 5, 2, 'Expected');
			Test\Assert::withinMargin(-4, 5, 10);
		}

		public function test_outside_margin(){
			Test\Assert::outsideMargin(10, 2, 9, 'Expected');
			Test\Assert::outsideMargin(10, 5, 2);
			Test\Assert::outsideMargin(-4, 5, 10, 'Expected');
		}

		public function test_identical(){
			Test\Assert::identical(1 + 1, 2);
			Test\Assert::identical(true, true);
			Test\Assert::identical(null, '', 'Expected');
			Test\Assert::identical(false, 0, 'Expected');
		}

		public function test_not_identical(){
			Test\Assert::notIdentical(1 + 1, 2, 'Expected');
			Test\Assert::notIdentical(true, true, 'Expected');
			Test\Assert::notIdentical(null, '');
			Test\Assert::notIdentical(false, 0);
		}

		public function test_reference(){
			$a = 'foo';
			$b = 'bar';
			$c =& $a;
			Test\Assert::reference($a, $c);
			Test\Assert::reference($a, $b, 'Expected');
			Test\Assert::reference($a, $a);
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
			Test\Assert::isClone($a, $b, 'Expected');
			Test\Assert::isClone($b, $d);
			Test\Assert::isClone($d, $d, 'Expected');
			Test\Assert::isClone($x, $y);
			$y['foo'] = 'foobar';
			Test\Assert::isClone($x, $y, 'Expected');
		}

		public function test_pattern(){
			Test\Assert::pattern('foo', '/foo/i');
			Test\Assert::pattern('foo', '/bar/i', 'Expected');
		}

		public function test_no_pattern(){
			Test\Assert::notPattern('foo', '/foo/i', 'Expected');
			Test\Assert::notPattern('foo', '/bar/i');
		}

		public function test_exception(){
			Test\Assert::exception(function(){
				throw new \Exception('exception');
			});
			Test\Assert::exception(function(){
				return 'foobar';
			}, 'Expected');
		}

		public function test_handle_exception(){
			throw new \Exception('ran into error');
		}

	}