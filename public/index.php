<?php

	/**
	 * Tea-Fueled Does is a php framework developed by Matthew Loberg (http://mloberg.com).
	 * Tea-Fueled Does is designed to be fast, in both development and performance.
	 */
	
	error_reporting(E_ERROR | E_WARNING | E_PARSE);

	// start the timer!
	define('START_TIME', microtime(true));
	define('START_MEM', memory_get_usage());
	
	// grab the environment from the .htaccess file
	$environment = $_SERVER['ENV'];
	
	// get the public dir
	$public_dir = __DIR__;
	
	// define the location of the app and content dir. Without the begin slash or trailing slash /
	$app_dir = '../tfd';
	$content_dir = '../content';
	
	// let's get this party started
	include_once('../bootstrap.php');

	// load environment config
	Config::load($environment);
	
	// make a new instance of our app class
	$app = new TFD\App();
	
	// and finally echo the site output
	
	echo $app->site();