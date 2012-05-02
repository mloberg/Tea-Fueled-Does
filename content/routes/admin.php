<?php

/**
 * As of TFD v3, we have removed the admin dashboard and
 * functionality from the core. We have instead placed it
 * here, where you can modify (or delete) it however you
 * want. Below is just an example of how you can use
 * included TFD classes to create your own admin dashboard.
 *
 * If you use this exactly as-is, you will need a table called
 * "users" with the fields, id, username, hash, and secret.
 */

use TFD\Auth;
use TFD\Config;
use TFD\Crypter;
use TFD\DB;
use TFD\Route;
use TFD\Response;
use TFD\Render;

Route::filter('auth', function() {
	$user = DB::table('users')->where('id', $_SESSION['user_id'])->limit(1)->get();
	if (empty($user)) redirect('/login');
	if (!Auth::valid($_SESSION['fingerprint'], $user['username'], $user['secret'])) {
		redirect('/login');
	}
});

// add a user
// Route::get('/admin/adduser', function() {
// 	var_dump(DB::table('users')->insert(array(
// 		'username' => $_GET['username'],
// 		'hash' => Crypter::hash($_GET['password']),
// 		'secret' => uniqid('', true)
// 	)));
// 	exit;
// });

Route::get('/login', function() {
	return Render::page(array('view' => 'login', 'dir' => ''));
});

Route::post('/login', function() {
	$user = DB::table('users')->where('username', $_POST['username'])->limit(1)->get();
	if (empty($user)) {
		redirect('/login');
	} elseif (Crypter::verify($_POST['password'], $user['hash'])) {
		$_SESSION['fingerprint'] = Auth::login($user['username'], $user['secret']);
		$_SESSION['user_id'] = $user['id'];
		redirect('/admin/');
	} else {
		redirect('/login');
	}
});

Route::get('/logout', function() {
	session_destroy();
	redirect('/');
});

Route::auto('/admin', 'admin', 'auth');

Route::get('/admin', function() {
	redirect('/admin/');
});
