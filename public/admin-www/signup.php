<? $this->load("elements");?>
<?= html::h2("Signup");?>
<?
	echo form::open("","post");
	echo form::label("fname","First Name");
	echo form::input("fname");
	echo html::br();
	echo form::label("lname","Last Name");
	echo form::input("lname");
	echo html::br();
	echo form::label("email","Email");
	echo form::input("email");
	echo html::br();
	echo form::label("username","Username");
	echo form::input("username");
	echo html::br();
	echo form::label("password","Password");
	echo form::password("password");
	echo html::br();
	echo form::label("password_conf","Password Confirmation");
	echo form::password("password_conf");
	echo html::br();
	$this->load("recaptcha");
	echo recaptcha_get_html();
	echo html::br();
	echo form::submit("Signup");
	echo form::close();
?>
<?= $errors;?>