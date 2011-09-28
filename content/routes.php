<?php

return array(

	'test' => function(){
		Benchmark::start('test');
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