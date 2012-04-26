<?php

use TFD\Config;
use TFD\Route;
use TFD\Response;
use TFD\Render;

Route::filter('before', function() {
	if (Config::get('site.maintenance') === true) {
		return (string)Response::make(Render::error('maintenance'));
	}
});

use TFD\DB;

Route::get('/db', function() {
	die(print_p(DB::table('posts')->get()));
});

Route::get('/test', function() {
	die(Test::foobar());
});

Route::get('/foo', function() {
	die('foobar!');
});
