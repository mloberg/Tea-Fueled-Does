<?php

require_once 'tfd/bootstrap.php';
require_once TEA_DIR.'init.php';

define('TEA_CONFIG', TEA_DIR.'config'.DIRECTORY_SEPARATOR);

if(!defined('STDIN')) define('STDIN', fopen("php://stdin", 'r'));

$tea = new Tea();

$tea->command($_SERVER['argv']);

echo PHP_EOL;
exit(0);