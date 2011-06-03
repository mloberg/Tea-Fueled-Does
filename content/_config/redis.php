<?php

/**
 * Set up your different Redis servers here.
 *
 * Some notes:
 *   Default Redis port is 6379
 *   If you do not have your Redis server password protected,
 *    leave the password field blank.
 */

if(ENVIRONMENT === 'DEVELOPMENT'){
	define('REDIS_HOST', 'localhost');
	define('REDIS_PORT', 6379);
	define('REDIS_PASS', '');
}elseif(ENVIRONMENT === 'TESTING'){
	define('REDIS_HOST', 'localhost');
	define('REDIS_PORT', 6379);
	define('REDIS_PASS', '');
}elseif(ENVIRONMENT === 'PRODUCTION'){
	define('REDIS_HOST', 'localhost');
	define('REDIS_PORT', 6379);
	define('REDIS_PASS', '');
}