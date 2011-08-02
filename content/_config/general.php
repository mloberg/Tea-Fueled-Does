<?php

// display the maintenance page
define('MAINTENANCE_MODE', true);

// general site config
define('SITE_TITLE', 'Tea-Fueled Does');
define('ADMIN_EMAIL', '');

// admin config
define('LOGIN_PATH', 'login');
define('ADMIN_PATH', 'admin');
define('USERS_TABLE', 'users'); // the MySQL table the user info is store in
define('AUTH_KEY', '123456'); // a custom key to validate users, change this
define('LOGIN_TIME', 3600); // time to stay logged in via cookie

// ajax config
define('MAGIC_AJAX_PATH', 'tfd-ajax');