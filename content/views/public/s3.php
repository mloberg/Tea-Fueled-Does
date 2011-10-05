<?php		
/*
	print_p(S3::create_bucket('foo', 'private', 'us-west-1'));
	print_p(S3::put_object(PUBLIC_DIR.'uploads/placeholder_thumb.png'));
	$file = file_get_contents(PUBLIC_DIR.'uploads/placeholder_thumb.png');
	print_p(S3::put_object_from_string($file, 'placeholder.png'));
*/