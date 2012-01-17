<?php namespace Content\Tests\Fixtures;

/**
 * Fixtures provide a way for you to load data into a test.
 * You can load any valid php type and use them across multiple tests
 */

return array(

	'paginator' => array(
		'foo', 'bar', 'foobar',
		'hello', 'world', 'hello world'
	),

	'mysql_insert' => array(
		'title' => 'foo',
		'content' => 'hello world!'
	),

	'mysql_multi_insert' => array(
		array('title', 'content'),
		array('bar', 'foo'),
		array('foobar', 'foobar'),
	)

);