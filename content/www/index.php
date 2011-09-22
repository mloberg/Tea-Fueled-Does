<?php
	if($_POST){
		$resp = ReCAPTCHA::check_answer();
		
		echo $resp->is_valid();
		echo $resp->error();
	}
?>
<p>Hello World!</p>

<p>Rendered in {time} seconds using {memory}mb of memory.</p>

<form action="" method="post">
<?php echo ReCAPTCHA::get_html();?>
<input type="submit" value="submit" name="submit" />
</form>