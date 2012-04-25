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

Route::get('/test', function() {
	die(Test::foobar());
});

Route::get('/foo', function() {
	die('foobar!');
});
