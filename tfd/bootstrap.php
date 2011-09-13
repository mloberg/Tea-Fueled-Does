<?php

/**
 * You probably don't need to edit this file unless you've extended TFD or moved some core files around.
 *
 * Best to leave this alone if you don't know what you're doing.
 */

// main directories
define('PUBLIC_DIR', realpath($public_dir).DIRECTORY_SEPARATOR);
define('APP_DIR', realpath($app_dir).DIRECTORY_SEPARATOR);
define('BASE_DIR', realpath(PUBLIC_DIR.'..').DIRECTORY_SEPARATOR);
define('CONTENT_DIR', realpath($content_dir).DIRECTORY_SEPARATOR);

// app directories
define('CORE_DIR', APP_DIR.'core'.DIRECTORY_SEPARATOR);
define('HELPER_DIR', APP_DIR.'helpers'.DIRECTORY_SEPARATOR);
define('LIBRARY_DIR', APP_DIR.'libraries'.DIRECTORY_SEPARATOR);
define('TEA_DIR', APP_DIR.'tea'.DIRECTORY_SEPARATOR);

// public directories
define('MODELS_DIR', CONTENT_DIR.'models'.DIRECTORY_SEPARATOR);
define('WEB_DIR', CONTENT_DIR.'www'.DIRECTORY_SEPARATOR);
define('MASTERS_DIR', CONTENT_DIR.'masters'.DIRECTORY_SEPARATOR);
define('PARTIALS_DIR', CONTENT_DIR.'partials'.DIRECTORY_SEPARATOR);
define('AJAX_DIR', CONTENT_DIR.'ajax'.DIRECTORY_SEPARATOR);
define('TEMPLATES_DIR', CONTENT_DIR.'templates'.DIRECTORY_SEPARATOR);

// some file helpers
define('EXT', '.php');
define('ADMIN_DIR', 'admin');
define('LOGIN_DIR', 'login');

// define some file paths
define('HOOKS_FILE', CONTENT_DIR.'hooks'.EXT);
define('CONF_FILE', CONTENT_DIR.'config'.EXT);
define('DEFAULT_MASTER', MASTERS_DIR.'master'.EXT);
define('MAINTENANCE_PAGE', MASTERS_DIR.'maintenance'.EXT);

// tfd version
define('TFD_VERSION', '1.5.1');

// include and load the config
include_once(CONF_FILE);
new Environment($environment);

// And now include the core file
include_once(CORE_DIR.'app.php');