<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<title>500 - Internal Server Error</title>
	<!--[if lt IE 9]>
		<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
</head>
<body>
	<header>
		<?php
			$messages = array('Whoops!', 'Oh no!', 'Ouch!');
			$message = $messages[mt_rand(0, 2)];
		?>
		<h1><?php echo $message;?></h1>
	</header>
	<div id="wrapper">
		<h2>The server made a boo-boo.</h2>
		<p>Something failed while we were handling your request. Would you like to go to our <a href="<?php echo Config::get('site.url');?>">home page</a> instead?</p>
	</div>
</body>
</html>