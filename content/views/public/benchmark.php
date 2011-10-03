<?php

	Benchmark::start('test');
	
	sleep(10);
	
	echo Benchmark::check('test');