<?php

use TFD\Upload\File;

return array(

	'GET test' => function(){
		Benchmark::start('test');
		return array('view' => 'index');
	},
	
	'users/[:num]' => function($match){
		echo Models\model::foo();
		// we have our matches available to use
		print_p($match);
		return array('view' => 'index');
	},
	
	'GET redirect' => function(){
		redirect('index');
	},
	
	'POST upload' => function(){
		$file = new File('file');
		print_p($file->type());
		print_p($file->is_type('pdf'));
		print_p($file->is_image());
		print_p($file->save(PUBLIC_DIR));
	},
	
	'GET admin/test' => function(){
		return array(
			'view' => 'index',
			'admin' => true,
			'master' => 'master'
		);
	}

);