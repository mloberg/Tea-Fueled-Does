<?php
	
	Redis::set('myval', 'Hello World');
	
	echo Redis::get('myval');