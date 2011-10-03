<?php

	if(Validate::text('new text')->required()->passed()){
		echo 'pass';
	}