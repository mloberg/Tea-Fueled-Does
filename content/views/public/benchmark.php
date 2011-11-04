<?php

	Benchmark::start('test');
	
	Benchmark::memory('test');
	
	$foo = array();
	
	for($i = 0; $i < 100; $i++){
		$foo[$i] = 'bar'.$i;
	}
	
	print_r($foo);
	
	echo '<br />'.Benchmark::check('test').'<br />';
	
	echo Benchmark::used_memory('test');