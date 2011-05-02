<? $this->load("elements");?>
<?= html::h2("Login Form");?>
<?= $errors;?>
<?
	echo form::open("","post");
	echo form::input('username',"Username", 'alt="Username"');
	echo html::br();
	echo form::password('password',"pass", 'alt="pass"');
	echo html::br();
	echo form::submit('Submit');
	echo form::close();
?>
<? $scripts = script("form");?>