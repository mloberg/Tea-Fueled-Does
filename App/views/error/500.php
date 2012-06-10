<?php
	$render->title = '500 - Internal Server Error';
	$messages = array('Whoops!', 'Oh no!', 'Ouch!');
?>
<h1><?php echo $messages[mt_rand(0, 2)];?></h1>
<h2>The server made a boo-boo.</h2>
<p>Something failed while we were handling your request. Would you like to go to our <a href="<?php echo Config::get('site.url');?>">home page</a> instead?</p>