<?php

use TFD\Core\Config;
use TFD\Core\Route;
use TFD\Core\Response;
use TFD\Core\Render;

Route::filter('before', function($request, $method) {
	if (Config::get('site.maintenance') === true) {
		return (string)Response::make(Render::error('maintenance'));
	}
});

Route::filter('after-example', function($result, $request, $method) {
	// 
});

use TFD\Core\DB;

Route::get('/db', function() {
	$db = new DB('posts');
	die(print_p($db->get()));
});

Route::get('/test', function() {
	die(Test::foobar());
});

Route::get('/foo', function() {
	die('foobar!');
});
