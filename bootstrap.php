<?php

/**
 * You probably don't need to edit this file unless you've extended TFD or moved some core files around.
 *
 * Best to leave this alone if you don't know what you're doing.
 */

define('EXT', '.php');

// main directories
define('PUBLIC_DIR', $public_dir.'/');
define('BASE_DIR', realpath('..').'/');
define('APP_DIR', realpath($app_dir).'/');
define('CONTENT_DIR', realpath($content_dir).'/');
unset($public_dir, $app_dir, $content_dir);

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

// include and load the config
include_once(CONTENT_DIR.'config'.EXT);
new Content\Environment($environment);
unset($environment);

// Autoloader
include_once(APP_DIR.'loader'.EXT);
spl_autoload_register(array('TFD\Loader', 'load'));

// create some class aliases
use TFD\Loader;
Loader::create_aliases(array(
	'CSS' => '\TFD\CSS',
	'JavaScript' => '\TFD\JavaScript',
	'Flash' => '\TFD\Flash',
	'MySQL' => '\TFD\DB\MySQL',
	'ReCAPTCHA' => '\TFD\Form\ReCAPTCHA',
	'Postmark' => '\TFD\Email\Postmark',
	'Image' => '\TFD\Library\Image',
	'Validate' => '\TFD\Library\Validate',
	'Template' => '\TFD\Library\Template',
	'Benchmark' => '\TFD\Benchmark',
	'Render' => '\TFD\Core\Render',
	'Redis' => '\TFD\Redis',
	'Upload' => '\TFD\Upload\File',
	'Config' => '\TFD\Config',
	'HTML' => '\TFD\HTML',
	'Form' => '\TFD\Form\HTML'
));
Loader::add_alias('PostmarkBatch', '\TFD\Email\PostmarkBatch', APP_DIR.'api/postmark'.EXT);