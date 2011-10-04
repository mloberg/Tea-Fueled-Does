<?php
	//print_p(S3::put_object(PUBLIC_DIR.'uploads/placeholder_thumb.png'));
	print_p(S3::create_bucket('mlobergfoo', 'private', 'us-west-1'));
?>
<!--
<form method="post" action="" enctype="multipart/form-data">
	<input type="file" name="file" />
	<input type="submit" name="submit" value="Upload" />
</form>
-->