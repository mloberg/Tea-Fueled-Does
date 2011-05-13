<?php

	/**
	 * Tea-Fueled Does is a php framework developed by Matthew Loberg (http://mloberg.com).
	 * Tea-Fueled Does is designed to be fast, in both development and performance.
	 * 
	 */
	
	// start the timer!
	$start_time = microtime(true);
	register_shutdown_function('timer');
	
	// define the location of the app and content dir. With the trailing slash (/)
	
	$app_dir = '../tfd/';
	$content_dir = '../content/';
	
	// then include the config file
	
	include_once($app_dir.'_config.php');
	
	$autoload = array(
		"helper" => "helpful"
	);
	
	// make a new instance of our app class
	
	$app = new TFD($autoload);
	
	// and finally echo the site output
	
	echo $app->site();
	
	// instead of echoing the time in the view, we do it once we are all done
	// that means we are more acurate then other frameworks =P
	
	function timer(){
		if(!$_GET['ajax']){
			global $start_time;
			echo "\n<script>console.log(\"Page rendered in ".(microtime(true) - $start_time)." seconds\");</script>";
		}
	}