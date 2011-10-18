<?php

	if(Validate::text('m@mloberg.com')->max_length(18)->email()->min_length(4)->passed()){
		echo 'pass';
	}