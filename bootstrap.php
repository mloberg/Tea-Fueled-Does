<?php

/*
| This file gets everything going.
| Unless you know what your doing, I wouldn't touch this file.
*/

/*
| Make sure our content and app directories actually exist.
*/

if (($app_dir = realpath($app_dir)) === false)
	throw new Exception('Application directory does not exist');
if (($core_dir = realpath($core_dir)) === false)
	throw new Exception('Content direcotry does not exist');

/*
| Let's define some core variables
*/

define('EXT', '.php');

define('PUBLIC_DIR', $public_dir.'/');
define('BASE_DIR', __DIR__.'/');
define('CORE_DIR', $core_dir.'/');
define('APP_DIR', $app_dir.'/');
define('LIBRARY_DIR', BASE_DIR.'Library/');
unset($public_dir, $app_dir, $core_dir);

define('TEA_DIR', CORE_DIR.'tea/');

define('MASTERS_DIR', APP_DIR.'masters/');
define('MODELS_DIR', APP_DIR.'models/');
define('PARTIALS_DIR', APP_DIR.'partials/');
define('TEMPLATES_DIR', APP_DIR.'templates/');
define('VIEWS_DIR', APP_DIR.'views/');

/*
| Include our functions and autoloader.
*/

include_once(CORE_DIR.'functions'.EXT);
include_once(CORE_DIR.'loader'.EXT);

/*
| Register our autoloader
*/

TFD\Core\Loader::register();

/*
| Set some core config items.
*/

TFD\Core\Config::load(array(
	'application.version' => '3.0a',
	'views.public' => 'public',
	'views.partials' => 'partials',
	'views.error' => 'error'
));

/*
| Set some autoloader aliases
*/

use TFD\Core\Loader;
Loader::alias(array(
	'CSS' => 'TFD\Core\CSS',
	'JavaScript' => 'TFD\Core\JavaScript',
	'Flash' => 'TFD\Core\Flash',
	'MySQL' => 'TFD\Core\DB\MySQL',
	'Render' => 'TFD\Core\Render',
	'Config' => 'TFD\Core\Config',
	'Cache' => 'TFD\Core\Cache',
	'Request' => 'TFD\Core\Request',
	'Model' => 'TFD\Core\Model',
	'Event' => 'TFD\Core\Event',
));

/*
| Set our error and exception handlers.
*/

set_exception_handler(function($e){
	TFD\Core\Event::fire('exception', $e);
});

set_error_handler(function($number, $error, $file, $line){
	if(error_reporting() === 0) return; // ignore @
	TFD\Core\Event::fire('error', $number, $error, $file, $line);
}, E_ALL ^ E_NOTICE);

/*
| Include app.php
*/

include_once(APP_DIR.'app'.EXT);

/*
| Set and start our Session handler.
*/

TFD\Core\Session::register();
session_start();

/*
| Load our routes.
*/

foreach (glob(APP_DIR.'routes/*') as $route) {
	if (is_file($route))
		include_once $route;
}
