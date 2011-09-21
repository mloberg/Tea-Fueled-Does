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

// app directories
define('CORE_DIR', APP_DIR.'core/');
define('HELPER_DIR', APP_DIR.'helpers/');
define('LIBRARY_DIR', APP_DIR.'library/');
define('TEA_DIR', APP_DIR.'tea/');

// public directories
define('ADMIN_DIR', CONTENT_DIR.'admin/');
define('AJAX_DIR', CONTENT_DIR.'ajax/');
define('LOGIN_DIR', CONTENT_DIR.'login/');
define('MASTERS_DIR', CONTENT_DIR.'masters/');
define('MODELS_DIR', CONTENT_DIR.'models/');
define('PARTIALS_DIR', CONTENT_DIR.'partials/');
define('TEMPLATES_DIR', CONTENT_DIR.'templates/');
define('WEB_DIR', CONTENT_DIR.'www/');

// content files
define('HOOKS_FILE', CONTENT_DIR.'hooks'.EXT);
define('CONF_FILE', CONTENT_DIR.'config'.EXT);
define('DEFAULT_MASTER', MASTERS_DIR.'master'.EXT);
define('MAINTENANCE_PAGE', MASTERS_DIR.'maintenance'.EXT);

// tfd version
define('TFD_VERSION', '2.0a');

// include and load the config
include_once(CONF_FILE);
new Environment($environment);

// And now include the core file
include_once(APP_DIR.'app'.EXT);

// our helper
include_once(HELPER_DIR.'helpful'.EXT);

// Autoloader
spl_autoload_register('TFD\App::__autoloader');

$class_aliases = array(
	'CSS' => '\TFD\Library\CSS',
	'MySQL' => '\TFD\DB\MySQL',
);