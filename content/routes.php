<?php

return array(

	'test' => function(){
		// it is possible to access TFD
		global $app;
		print_p($app->mysql->get('users'));
		return array('file' => 'index');
	},
	
	'users/[:num]' => function($match){
		// we have our matches available to use
		print_p($match);
		return array('file' => 'index');
	},
	
	'redirect' => function(){
		redirect('index');
	}

);