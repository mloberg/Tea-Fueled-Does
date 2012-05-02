<?php

Event::listen('exception', function($e) {
	\TFD\Exception\Handler::make($e)->handle();
});

Event::listen('error', function($number, $error, $file, $line) {
	\TFD\Exception\Handler::make(new \ErrorException($error, $number, 0, $file, $line))->handle();
});

Event::listen('404', function() {
	return (string)TFD\Response::error('404');
});

Event::listen('spinup', function() {
	// 
});

Event::listen('pre_render', function() {
	CSS::load('reset');
});

Event::listen('render', function() {
	// 
});

Event::listen('post_render', function() {
	// 
});

Event::listen('partial', function() {
	// 
});

Event::listen('spindown', function() {
	// 
});

use TFD\Loader;

Loader::alias(array(
	'App' => 'Content\Library\App'
));

use TFD\Config;

Config::group(array(
	'site.maintenance' => false,
	'site.title' => 'Tea-Fueled Does',

	'db.class' => 'MySQL',
	'render.master' => 'master', // default master
	'auth.key' => '4f0712cd96a93',
	'crypter.cost' => 10, // default cost for the crypter class
	'cache.key' => '',

	// ReCAPTCHA - http://www.google.com/recaptcha
	'recaptcha.public_key' => '',
	'recaptcha.private_key' => '',
	
	// Postmark - http://postmarkapp.com/
	'postmark.api_key' => '',
	'postmark.from' => '',
	'postmark.reply_to' => '',
	
	// Amazon S3 - http://aws.amazon.com/s3/
	's3.access_key' => '',
	's3.secret_key' => '',
	's3.bucket' => '',
	's3.acl' => 'private'
));

Config::group('development', array(
	'site.url' => 'http://localhost', // without trailing slash
	
	'application.debug' => true,
	'error.log' => false,
	'error.detailed' => true,
	
	'mysql.host' => '127.0.0.1', // do not use "localhost" (use 127.0.0.1 instead)
	'mysql.port' => 3306, // MySQL default is 3306
	'mysql.user' => 'root',
	'mysql.pass' => 'root',
	'mysql.db' => 'tfd',
	
	'redis.host' => '',
	'redis.port' => 6379,
	'redis.pass' => '', // blank for none
	
	'cache.driver' => 'file',
	'cache.dir' => BASE_DIR.'cache/',
	
	'memcached.class' => '', // defaults to memcache
	'memcached.servers' => array(
		array(
			'host' => '',
			'port' => 11211,
			'weight' => 100,
		)
	)
));

Config::group('testing', array(
	'site.url' => '', // without trailing slash
	
	'application.debug' => true,
	'error.log' => BASE_DIR.'error.log',
	'error.detailed' => true,
	
	'mysql.host' => '',
	'mysql.port' => 3306,
	'mysql.user' => '',
	'mysql.pass' => '',
	'mysql.db' => '',
	
	'redis.host' => '',
	'redis.port' => 6379,
	'redis.pass' => '', // blank for none
	
	'cache.driver' => 'file',
	'cache.dir' => BASE_DIR.'cache/',
	
	'memcached.class' => '', // defaults to memcache
	'memcached.servers' => array(
		array(
			'host' => '',
			'port' => 11211,
			'weight' => 100,
		)
	)
));

Config::group('production', array(
	'site.url' => '', // without trailing slash
	
	'application.debug' => false,
	'error.log' => BASE_DIR.'error.log',
	'error.detailed' => false,
	
	'mysql.host' => '',
	'mysql.port' => 3306,
	'mysql.user' => '',
	'mysql.pass' => '',
	'mysql.db' => '',
	
	'redis.host' => '',
	'redis.port' => 6379,
	'redis.pass' => '', // blank for none
	
	'cache.driver' => 'file',
	'cache.dir' => BASE_DIR.'cache/',
	
	'memcached.class' => '', // defaults to memcache
	'memcached.servers' => array(
		array(
			'host' => '',
			'port' => 11211,
			'weight' => 100,
		)
	)
));
