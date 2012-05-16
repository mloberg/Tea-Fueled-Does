<?php

/*
| This file gets everything going.
| Unless you know what your doing, I wouldn't touch this file.
*/

/*
| Make sure our content and app directories actually exist.
*/

if (($app_dir = realpath($public_dir.'/'.$app_dir)) === false)
	throw new Exception('Application directory does not exist');
if (($content_dir = realpath($public_dir.'/'.$content_dir)) === false)
	throw new Exception('Content direcotry does not exist');

/*
| Let's define some core variables
*/

define('EXT', '.php');

define('PUBLIC_DIR', $public_dir.'/');
define('BASE_DIR', __DIR__.'/');
define('APP_DIR', $app_dir.'/');
define('CONTENT_DIR', $content_dir.'/');
unset($public_dir, $app_dir, $content_dir);

define('LIBRARY_DIR', CONTENT_DIR.'library/');
define('TEA_DIR', APP_DIR.'tea/');

define('MASTERS_DIR', CONTENT_DIR.'masters/');
define('MODELS_DIR', CONTENT_DIR.'models/');
define('PARTIALS_DIR', CONTENT_DIR.'partials/');
define('TEMPLATES_DIR', CONTENT_DIR.'templates/');
define('VIEWS_DIR', CONTENT_DIR.'views/');

/*
| Include our functions and autoloader.
*/

include_once(APP_DIR.'functions'.EXT);
include_once(APP_DIR.'loader'.EXT);

/*
| Register our autoloader
*/

spl_autoload_register(array('TFD\Loader', 'autoload'));

/*
| Set some core config items.
*/

TFD\Config::load(array(
	'application.version' => '3.0a',
	'views.public' => 'public',
	'views.partials' => 'partials',
	'views.error' => 'error'
));

/*
| Set some autoloader aliases
*/

use TFD\Loader;
Loader::alias(array(
	'CSS' => 'TFD\CSS',
	'JavaScript' => 'TFD\JavaScript',
	'Flash' => 'TFD\Flash',
	'MySQL' => 'TFD\DB\MySQL',
	'Render' => 'TFD\Render',
	'Config' => 'TFD\Config',
	'Cache' => 'TFD\Cache',
	'Request' => 'TFD\Request',
	'Model' => 'TFD\Model',
	'Event' => 'TFD\Event',
));
Loader::alias('PostmarkBatch', '\TFD\PostmarkBatch', APP_DIR.'postmark'.EXT);
Loader::alias('PostmarkBounces', '\TFD\PostmarkBounces', APP_DIR.'postmark'.EXT);

/*
| Set our error and exception handlers.
*/

set_exception_handler(function($e){
	\TFD\Event::fire('exception', $e);
});

set_error_handler(function($number, $error, $file, $line){
	if(error_reporting() === 0) return; // ignore @
	\TFD\Event::fire('error', $number, $error, $file, $line);
}, E_ALL ^ E_NOTICE);

/*
| Include app.php
*/

include_once(CONTENT_DIR.'app'.EXT);

/*
| Set and start our Session handler.
*/

TFD\Session::register();
session_start();

/*
| Load our routes.
*/

foreach (glob(CONTENT_DIR.'routes/*') as $route) {
	if (is_file($route))
		include_once $route;
}
