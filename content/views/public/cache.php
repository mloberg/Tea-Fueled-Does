<?php

use TFD\Cache\File;

//File::set('foo', 'bar', 10);

print_p(Cache::has('foo'));
	
//	Cache::store('foo', TFD\Core\Render::partial('test')->render(), 300);
	
//	print_p(Cache::get('foo'));
	
//	Cache::delete('foo');