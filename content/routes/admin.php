<?php

use TFD\Config;
use TFD\Route;
use TFD\Response;
use TFD\Render;

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
	return Render::template('foo.statche');
	redirect('/admin/');
});
