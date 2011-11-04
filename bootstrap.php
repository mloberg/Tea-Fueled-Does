<?php

/**
 * This file gets everything going. Unless you know what your doing, I wouldn't touch this file.
 */

// our file extension
define('EXT', '.php');

// main directories
define('PUBLIC_DIR', $public_dir.'/');
define('BASE_DIR', __DIR__.'/');
define('APP_DIR', realpath($app_dir).'/');
define('CONTENT_DIR', realpath($content_dir).'/');
unset($public_dir, $app_dir, $content_dir); // cleanup the global namespace

// app directories
define('FUNCTIONS_DIR', APP_DIR.'functions/');
define('LIBRARY_DIR', APP_DIR.'library/');
define('TEA_DIR', APP_DIR.'tea/');

// public directories
define('MASTERS_DIR', CONTENT_DIR.'masters/');
define('MODELS_DIR', CONTENT_DIR.'models/');
define('PARTIALS_DIR', CONTENT_DIR.'partials/');
define('TEMPLATES_DIR', CONTENT_DIR.'templates/');
define('VIEWS_DIR', CONTENT_DIR.'views/');

// our helper
include_once(FUNCTIONS_DIR.'helpful'.EXT);

// Config class
include_once(APP_DIR.'config'.EXT);
// load some default config options
TFD\Config::load(array(
	'application.version' => '2.0a',
	'application.maintenance_page' => MASTERS_DIR.'maintenance'.EXT,
	'render.default_master' => MASTERS_DIR.'master'.EXT,
	
	'views.admin' => 'admin',
	'views.login' => 'login',
	'views.public' => 'public',
	'views.protected' => 'protected',
	'views.partials' => 'partials',
	'views.error' => 'error'
));

// include our environment file (with our application config)
include_once(CONTENT_DIR.'config'.EXT);
new Content\Environment($environment);
unset($environment); // cleanup the global namespace

// Autoloader
include_once(APP_DIR.'loader'.EXT);
spl_autoload_register(array('TFD\Loader', 'load'));

// Error Handlers
set_exception_handler(function($e){
	\TFD\Exception\Handler::make($e)->handle();
});

set_error_handler(function($number, $error, $file, $line){
	\TFD\Exception\Handler::make(new \ErrorException($error, $number, 0, $file, $line))->handle();
}, E_ALL ^ E_NOTICE);

// create some class aliases
use TFD\Loader;
Loader::create_aliases(array(
	'CSS' => 'TFD\CSS',
	'JavaScript' => 'TFD\JavaScript',
	'Flash' => 'TFD\Flash',
	'MySQL' => 'TFD\DB\MySQL',
	'ReCAPTCHA' => 'TFD\Form\ReCAPTCHA',
	'Postmark' => 'TFD\Postmark',
	'Image' => 'TFD\Image',
	'Validate' => 'TFD\Form\Validate',
	'Template' => 'TFD\Template',
	'Benchmark' => 'TFD\Benchmark',
	'Render' => 'TFD\Core\Render',
	'Redis' => 'TFD\Redis',
	'Upload' => 'TFD\Upload\File',
	'Config' => 'TFD\Config',
	'HTML' => 'TFD\HTML',
	'Form' => 'TFD\Form\HTML',
	'S3' => 'TFD\S3',
	'Cache' => 'TFD\Cache'
));
Loader::add_alias('PostmarkBatch', '\TFD\PostmarkBatch', APP_DIR.'api/postmark'.EXT);
if(APP_DIR !== BASE_DIR.'tfd/') Loader::app_dir(str_replace(BASE_DIR, '', APP_DIR));
if(CONTENT_DIR !== BASE_DIR.'content/') Loader::content_dir(str_replace(BASE_DIR, '', CONTENT_DIR));