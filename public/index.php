<?php

/*
 * Tea-Fueled Does - An awesome PHP 5.3 framework
 *
 * @version 3.0a
 * @author Matthew Loberg <loberg.matt@gmail.com> (http://mloberg.com)
 * @link http://teafueleddoes.com
 */

/*
| These are for testing the framework
*/

define('START_TIME', microtime(true));
define('START_MEM', memory_get_usage());

/*
| We need to know where the public, app (tfd), and content
| directories are located (relative to public). Please leave
| off the beginning and trailing slash.
*/

$public_dir = __DIR__;
$app_dir = '../app';
$core_dir = '../core';

/*
| Include our bootstrap file that sets up the application
| and includes some core files, and app.php.
*/
include_once('../bootstrap.php');

/*
| Our application get's rendered when we call the site method
| of our App class.
*/

$app = new TFD\Core\App();
echo $app->site();

/*
| This is an example of extending a core application file.
*/

// echo $app->test();

/*
| Here to benchmark the framework.
| This will be removed when TFD get's pushed to the master branch.
*/

echo "\n<br />".round(microtime(true) - START_TIME, 4);
