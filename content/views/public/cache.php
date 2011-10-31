<?php

	use TFD\Cache\APC as Cache;
	
	echo (Cache::has('foo')) ? "true" : "false";