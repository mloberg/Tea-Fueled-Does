<?php

// store an item for 5 minutes
Cache::store('foo', 'bar', 300);

// Check for item
if(Cache::has('foo')){
	echo 'foo exists!';
}

// delete item
Cache::clear('foo');