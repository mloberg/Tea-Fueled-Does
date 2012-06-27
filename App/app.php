<?php

/*
| app.php's purpose is to hold the configuration for the
| application along with some other core application code.
*/

/*
| Set PHP's error reporting level.
*/

error_reporting(E_ERROR | E_WARNING | E_PARSE);

/*
| Preloaded CSS stylesheets for TFD\Core\CSS.
|
| Syntax:
|   name => source
*/

CSS::library(array(
	'reset' => '/css/reset.css',
	'jquery-ui' => '/css/ui-lightness/jquery-ui.css',
));

/*
| Preloaded JavaScripts for TFD\Core\JavaScript.
|
| The syntax for this is a little more complex
| then the CSS list, because each script has
| multiple options.
|
| Options:
|   Source (Required): Location of the script.
|   Ready: Window ready script. ++SOURCE++ will
|     be repalced with scripts loaded through
|     JavaScript::ready
|   Depends: An array of dependencies
*/

JavaScript::library(array(
	'mootools' => array(
		'source' => '/js/mootools-core.min.js',
		'ready' => 'window.addEvent("domready",function(){ ++SOURCE++ })'
	),
	'mootools-more' => array(
		'source' => '/js/mootools-more.min.js',
		'depends' => array('mootools')
	),
	'jquery' => array(
		'source' => '/js/jquery.min.js',
		'ready' => '$(document).ready(function(){ ++SOURCE++ })'
	),
	'jquery-ui' => array(
		'source' => '/js/jquery-ui.min.js',
		'depends' => array()
	),
	'dojo' => array(
		'source' => 'http://ajax.googleapis.com/ajax/libs/dojo/1.6.1/dojo/dojo.xd.js',
		'ready' => 'dojo.ready(function(){ ++SOURCE++ })'
	),
));

/*
| Set up our event listeners
*/

Event::listen('exception', function($e) {
	TFD\Core\Exception\Handler::make($e)->handle();
});

Event::listen('error', function($number, $error, $file, $line) {
	TFD\Core\Exception\Handler::make(new \ErrorException($error, $number, 0, $file, $line))->handle();
});

Event::listen('404', function() {
	return (string)TFD\Core\Response::error('404');
});

Event::listen('spinup', function() {
	// 
});

Event::listen('pre_render', function() {
	// 
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

/*
| If you are extending core classes, you
| need to add an alias to use them.
*/

use TFD\Core\Loader;

Loader::alias(array(
	// 'App' => 'Content\Library\App'
));

/*
| Set application and environment config items.
*/

use TFD\Core\Config;

Config::group(array(
	'site.maintenance' => false,
	'site.title' => 'Tea-Fueled Does',

	'db.class' => 'MySQL',
	'render.master' => 'master', // default master
	'auth.key' => '4f0712cd96a93',
	'crypter.cost' => 10, // default cost for the crypter class

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

	'session.handler' => '',
	'session.save_path' => '',

	'cache.driver' => 'file',
	'cache.save_path' => BASE_DIR.'storage/cache/',
	
	'mysql.host' => '127.0.0.1', // do not use "localhost" (use 127.0.0.1 instead)
	'mysql.port' => 3306, // MySQL default is 3306
	'mysql.user' => 'root',
	'mysql.pass' => 'root',
	'mysql.db' => 'tfd',
	
	'redis.host' => '127.0.0.1',
	'redis.port' => 6379,
	'redis.auth' => '', // blank for none
	
	'cache.driver' => 'file',
	
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

	'session.handler' => '',
	'session.save_path' => '',

	'cache.driver' => '',
	'cache.save_path' => '',
	
	'mysql.host' => '',
	'mysql.port' => 3306,
	'mysql.user' => '',
	'mysql.pass' => '',
	'mysql.db' => '',
	
	'redis.host' => '',
	'redis.port' => 6379,
	'redis.auth' => '', // blank for none
	
	'cache.driver' => 'file',
	
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

	'session.handler' => '',
	'session.save_path' => '',

	'cache.driver' => '',
	'cache.save_path' => '',
	
	'mysql.host' => '',
	'mysql.port' => 3306,
	'mysql.user' => '',
	'mysql.pass' => '',
	'mysql.db' => '',
	
	'redis.host' => '',
	'redis.port' => 6379,
	'redis.auth' => '', // blank for none
	
	'cache.driver' => 'file',
	
	'memcached.class' => '', // defaults to memcache
	'memcached.servers' => array(
		array(
			'host' => '',
			'port' => 11211,
			'weight' => 100,
		)
	)
));

/*
| Set the environment and load environment config.
*/

$environments = array(
	'development' => array('localhost', '*.dev'),
	'testing' => array('test.example.com'),
	'production' => array('example.com'),
);

Config::load(Request::detect_env($environments, $_SERVER['HTTP_HOST']));

/*
| Set the request string.
*/

Request::make($_GET['tfd_request']);
