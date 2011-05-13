<?php

/**
 * You probably don't need to edit this file unless you've extended TFD or moved some core files around.
 *
 * Best to leave this alone if you don't know what you're doing.
 */

// main directories
define('PUBLIC_DIR', $_SERVER['DOCUMENT_ROOT'] . '/');
define('APP_DIR', realpath(PUBLIC_DIR.$app_dir) . '/');
define('BASE_DIR', realpath(APP_DIR.'..') . '/');
define('CONTENT_DIR', realpath(PUBLIC_DIR.$content_dir) . '/');

// app directories
define('CORE_DIR', APP_DIR . 'core/');
define('HELPER_DIR', APP_DIR . 'helpers/');
define('LIBRARY_DIR', APP_DIR . 'libraries/');

// public directories
//define('MODULE_DIR', CONTENT_DIR . 'modules/');
define('MODELS_DIR', CONTENT_DIR . 'models/');
define('WEB_DIR', CONTENT_DIR . 'www/');
define('MASTERS_DIR', CONTENT_DIR . 'masters/');
define('PARTIALS_DIR', CONTENT_DIR . 'partials/');
define('AJAX_DIR', CONTENT_DIR . 'ajax/');

define('EXT', '.php');

// define some file paths
define('DEFAULT_MASTER', MASTERS_DIR . 'master' . EXT);
define('HOOKS_FILE', CONTENT_DIR . 'hooks' . EXT);

// include all the other config files
define('CONF_DIR', CONTENT_DIR . '_config/');
foreach(glob(CONF_DIR."*".EXT) as $conf){
	include_once($conf);
}

// And now include the core file
include_once(CORE_DIR.'app.php');