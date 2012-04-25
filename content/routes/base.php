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

Route::filter('auth', function() {
	// 
});

Route::get('/login', function() {
	// 
});

Route::post('/login', function() {
	// 
});

Route::get('/logout', function() {
	// 
});

Route::get('/admin', 'auth', function() {
	// route with filter
});

Route::get('/test', function() {
	Flash::message("foobar");
	return array('view' => 'index');
});

Route::get('/foo', function() {
	die('foobar!');
});
