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

Route::auto('/admin', 'admin', 'auth');

Route::get('/admin', function() {
	redirect('/admin/');
});

Route::get('/test', function() {
	die(Test::foobar());
});

Route::get('/foo', function() {
	die('foobar!');
});
