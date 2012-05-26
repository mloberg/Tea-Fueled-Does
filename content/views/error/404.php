<?php
	$render->title = '404 - Page Not Found';
	$messages = array("We're lost.", "This doesn't look familiar.", 'We need a map.');
	$apologies = array('This is embarrassing.', "Don't give up on us.", "We're really sorry.");
?>
<h1><?php echo $messages[mt_rand(0, 2)];?></h1>
<h2><?php echo $apologies[mt_rand(0, 2)];?></h2>
<p>We couldn't find the resource you requested. Would you like to go to our <a href="<?php echo Config::get('site.url');?>">home page</a> instead?</p>