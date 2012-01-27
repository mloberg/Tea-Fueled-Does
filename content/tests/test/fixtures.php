<?php namespace Content\Tests\Test;

	use TFD\Test;

	/**
	 * Fixtures are collections of data.
	 * They are designed to be used across tests
	 * and can be used in place of a db connection.
	 */

	class Fixtures extends Test{
		
		const name = 'Fixtures';

		public function test_fixture(){
			$fixture = Test\Fixture::load('fixture');
			Test\Assert::equal($fixture->foo, 'bar');
			Test\Assert::type($fixture->bar, 'array');
			Test\Assert::type($fixture->x, 'string');
			Test\Assert::isEmpty($fixture->x);

			$foo = $fixture->foobar;
			Test\Assert::equal($foo->foobar(), 'foobar');
		}

	}