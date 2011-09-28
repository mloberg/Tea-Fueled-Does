<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<title>404 - Not Found</title>
	<!--[if lt IE 9]>
		<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
</head>
<body>
	<header>
		<?php
			$messages = array("We're lost.", "This doesn't look familiar.", 'We need a map.');
			$message = $messages[mt_rand(0, 2)];
		?>
		<h1><?php echo $message;?></h1>
	</header>
	<div id="wrapper">
		<?php
			$apologies = array('This is embarrassing.', "Don't give up on us.", "We're really sorry.");
			$apology = $apologies[mt_rand(0, 2)];
		?>
		<h2><?php echo $apology;?></h2>
		<p>We couldn't find the resource you requested. Would you like to go to our <a href="<?php echo BASE_URL;?>">home page</a> instead?</p>
	</div>
</body>
</html>