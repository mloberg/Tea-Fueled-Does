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
			$this->assertEqual($fixture->foo, 'bar');
			$this->assertIsA($fixture->bar, 'array');
			$this->assertIsA($fixture->x, 'string');
			$this->assertEmpty($fixture->x);

			$foo = $fixture->foobar;
			$this->assertEqual($foo->foobar(), 'foobar');
		}

	}