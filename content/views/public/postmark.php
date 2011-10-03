<?php

	$postmark = new Postmark();
	
	$bounces = $postmark->get_bounces();
	
	print_p($bounces->response());