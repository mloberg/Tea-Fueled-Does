<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<title><?php echo $title;?></title>
	<?php echo CSS::render();?>
	<!--[if lt IE 9]>
		<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
</head>
<body>
<?php //echo $this->flash->render();?>
<div id="wrapper">
<?php echo $content;?>

</div>
<?php //$this->javascript->echo_scripts();?>
</body>
</html>