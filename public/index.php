<?php

	$app_dir = '../tfd/';
	
	// then include the config file
	
	include_once($app_dir.'config/config.php');
	
	$autoload = array(
		"helper" => "helpful"
	);
	
	// make a new instance of our app class
	
	$app = new TFD($autoload);
	
	// and finally echo the site output
	
	echo $app->site();