<?php

/**
 * This file gets everything going. Unless you know what your doing, I wouldn't touch this file.
 */

// our file extension
define('EXT', '.php');

// main directories
if (($app_dir = realpath($public_dir.'/'.$app_dir)) === false)
	throw new Exception('Application directory does not exist');
if (($content_dir = realpath($public_dir.'/'.$content_dir)) === false)
	throw new Exception('Content direcotry does not exist');

define('PUBLIC_DIR', $public_dir.'/');
define('BASE_DIR', __DIR__.'/');
define('APP_DIR', $app_dir.'/');
define('CONTENT_DIR', $content_dir.'/');
unset($public_dir, $app_dir, $content_dir);

// app directories
define('LIBRARY_DIR', APP_DIR.'library/');
define('TEA_DIR', APP_DIR.'tea/');

// public directories
define('MASTERS_DIR', CONTENT_DIR.'masters/');
define('MODELS_DIR', CONTENT_DIR.'models/');
define('PARTIALS_DIR', CONTENT_DIR.'partials/');
define('TEMPLATES_DIR', CONTENT_DIR.'templates/');
define('VIEWS_DIR', CONTENT_DIR.'views/');

include_once(APP_DIR.'functions'.EXT);

// Config class
include_once(APP_DIR.'config'.EXT);

TFD\Config::load(array(
	'application.version' => 'pre-3',
	'application.maintenance_page' => MASTERS_DIR.'maintenance'.EXT,
	'render.default_master' => MASTERS_DIR.'master'.EXT,
	
	'views.admin' => 'admin',
	'views.login' => 'login',
	'views.public' => 'public',
	'views.protected' => 'protected',
	'views.partials' => 'partials',
	'views.error' => 'error'
));

// Autoloader
include_once(APP_DIR.'loader'.EXT);
spl_autoload_register(array('TFD\Loader', 'load'));

// create some class aliases
use TFD\Loader;
Loader::alias(array(
	'CSS' => 'TFD\CSS',
	'JavaScript' => 'TFD\JavaScript',
	'Flash' => 'TFD\Flash',
	'MySQL' => 'TFD\DB\MySQL',
	'ReCAPTCHA' => 'TFD\ReCAPTCHA',
	'Postmark' => 'TFD\Postmark',
	'Image' => 'TFD\Image',
	'Validate' => 'TFD\Validate',
	'Template' => 'TFD\Template',
	'Benchmark' => 'TFD\Benchmark',
	'Render' => 'TFD\Render',
	'Redis' => 'TFD\Redis',
	'Config' => 'TFD\Config',
	'HTML' => 'TFD\HTML',
	'Form' => 'TFD\Form',
	'S3' => 'TFD\S3',
	'Cache' => 'TFD\Cache',
	'Paginator' => 'TFD\Paginator',
	'Request' => 'TFD\Request',
	'File' => 'TFD\File',
	'Model' => 'TFD\Model',
	'RSS' => 'TFD\RSS',
	'Event' => 'TFD\Event',
));
Loader::alias('PostmarkBatch', '\TFD\PostmarkBatch', APP_DIR.'postmark'.EXT);
Loader::alias('PostmarkBounces', '\TFD\PostmarkBounces', APP_DIR.'postmark'.EXT);

// Load app.php
include_once(CONTENT_DIR.'app'.EXT);

// Error Handlers
set_exception_handler(function($e){
	\TFD\Event::fire('exception', $e);
});

set_error_handler(function($number, $error, $file, $line){
	if(error_reporting() === 0) return; // ignore @
	\TFD\Event::fire('error', $number, $error, $file, $line);
}, E_ALL ^ E_NOTICE);

// load routes

foreach (new DirectoryIterator(CONTENT_DIR.'routes/') as $route) {
	if ($route->isFile())
		include_once($route->getPathName());
}
