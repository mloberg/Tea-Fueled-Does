<?php

	$postmark = new Postmark();
	
	$bounces = $postmark->get_bounces();
	
	print_p($bounces->response());

/*
	$postmark = new PostmarkBatch();
	
	$postmark->to('loberg.matt@gmail.com')->subject('Postmark Test')->message("Testing TFD's Postmark Library")->add();
	$postmark->to('matt@dkyinc.com')->subject('Postmark Test')->message("A test of TFD's Postmark library.")->add();
	
	$resp = $postmark->send();
	
	print_p($resp->sent());
	print_p($resp->error());
	print_p($resp->http_code());
	print_p($resp->response());
*/