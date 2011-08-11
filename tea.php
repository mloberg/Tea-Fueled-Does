<?php

// some settings and stuff
$environment = 'DEVELOPMENT';
$app_dir = '../tfd/';
$content_dir = '../content/';

require_once 'tfd/bootstrap.php';
require_once TEA_DIR.'init.php';

// some Tea settings
define('TEA_CONFIG', TEA_DIR.'config'.DIRECTORY_SEPARATOR);
if(!defined('STDIN')) define('STDIN', fopen("php://stdin", 'r'));
error_reporting(E_ERROR | E_PARSE);


$app = new TFD();
$tea = new Tea();

// run the command
$tea->command($_SERVER['argv']);

// exit the script properly
echo PHP_EOL;
exit(0);