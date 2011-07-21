<?php

	/**
	 * Tea-Fueled Does is a php framework developed by Matthew Loberg (http://mloberg.com).
	 * Tea-Fueled Does is designed to be fast, in both development and performance.
	 */
	
	// start the timer!
	define('START_TIME', microtime(true));
	define('START_MEM', memory_get_usage());
	
	// define the location of the app and content dir. Without the begin slash and with the trailing slash (/)
	
	$app_dir = '../tfd/';
	$content_dir = '../content/';
	
	// then include the config file
	
	include_once($app_dir.'bootstrap.php');
	
	$autoload = array(
		"helper" => "helpful"
	);
	
	// make a new instance of our app class
	
	$app = new TFD($autoload);
	
	// and finally echo the site output
	
	echo $app->site();