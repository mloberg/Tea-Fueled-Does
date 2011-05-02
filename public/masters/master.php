<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<title><?= $title;?></title>
	<?= stylesheet_link_tag('reset');?>
	<?= $stylesheets;?>
	<!--[if lt IE 9]>
		<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
</head>
<body>
<div id="wrapper">
<?= $content;?>
</div>
<?= mootools();?>
<?= $scripts;?>
</body>
</html>