<?php
	$img = S3::authenticated_url('placeholder_thumb.png', 3600);
		
	echo '<img src="'.$img.'" />';
/*
	print_p(S3::create_bucket('mlobergfoo', 'private', 'us-west-1'));
	print_p(S3::put_object(PUBLIC_DIR.'uploads/placeholder_thumb.png'));
	$file = file_get_contents(PUBLIC_DIR.'uploads/placeholder_thumb.png');
	print_p(S3::put_object_from_string($file, 'placeholder.png'));
*/
?>
<!--
<form method="post" action="" enctype="multipart/form-data">
	<input type="file" name="file" />
	<input type="submit" name="submit" value="Upload" />
</form>
-->