<?php

	$redis = new Redis();
	
	$redis->set('myval', 'Hello World');
	
	echo $redis->get('myval');