<?php

	$resp = Postmark::make()->to('mail@example.com')->send(array('Subject' => 'Test', 'TextBody' => 'foobar'));
	
	if(!$resp->sent()){
		echo $resp->error();
	}
	
	print_p($resp->response());
	
	// bounces
	print_p(PostmarkBounces::make()->get()->response());