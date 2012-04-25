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

use TFD\Loader;

Loader::alias(array(
	'App' => 'Content\Library\App'
));

use TFD\Config;

Config::group(array(
	'site.maintenance' => false,
	'site.title' => 'Tea-Fueled Does',
	
	'admin.login' => '/login',
	'admin.logout' => '/logout',
	'admin.path' => '/admin',
	'admin.table' => 'users',
	'admin.auth_key' => '4f0712cd96a93',
	'admin.login_time' => 3600,
	'admin.cost' => 12, // rounds for hashing passwords
	
	'crypter.rounds' => 10, // default rounds for the crypter class
	
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
