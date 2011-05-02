<?php

/**
 * You probably don't need to edit this file unless you've extended TFD or moved some core files around.
 *
 * Best to leave this alone if you don't know what you're doing.
 */

// main directories
define('PUBLIC_DIR', $_SERVER['DOCUMENT_ROOT'].'/');
define('APP_DIR', realpath(PUBLIC_DIR.$app_dir).'/');
define('BASE_DIR', realpath(APP_DIR.'..').'/');

// app directories
define('CORE_DIR', APP_DIR . 'core/');
define('HELPER_DIR', APP_DIR . 'helpers/');
define('LIBRARY_DIR', APP_DIR . 'libraries/');

// public directories
//define('MODULE_DIR', CONTENT_DIR . 'modules/');
define('MODELS_DIR', PUBLIC_DIR . 'models/');
define('WEB_DIR', PUBLIC_DIR . 'www/');
define('MASTERS_DIR', PUBLIC_DIR . 'masters/');
define('PARTIALS_DIR', PUBLIC_DIR . 'partials/');

define('EXT', '.php');

// define some file paths
define('DEFAULT_MASTER', MASTERS_DIR . 'master' . EXT);
define('HOOKS_FILE', PUBLIC_DIR . 'hooks' . EXT);

// include all the other config files
include_once('general.php');
include_once('environments.php');
include_once('api-keys.php');

// And now include the core file
include_once(CORE_DIR.'app.php');