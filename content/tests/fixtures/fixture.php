<?php namespace Content\Tests\Fixtures;

/**
 * Fixtures provide a way for you to load data into a test.
 * You can load any valid php type and use them across multiple tests
 */

return array(

	'foo' => 'bar', // strings

	'bar' => array( // arrays
		'a' => 'b',
		'c' => 'd'
	),

	'x' => function(){ // you can use functions to return more advance things
		return '';
	},

	'foobar' => new Foo()

);

class Foo{
	
	public function foobar(){
		return 'foobar';
	}

}