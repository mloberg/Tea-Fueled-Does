<?php namespace TFD;

	use TFD\DB\MySQL;
	use Content\Hooks;
	use TFD\Core\Render;
	use TFD\Core\Response;
	
	class Admin{
	
		private static function __validate_session_fingerprint(){
			try{
				$user = MySQL::table(Config::get('admin.table'))->where('id', '=', $_SESSION['user_id'])->limit(1)->get('secret');
			}catch(Exception $e){
				echo '<pre>'.$e->getMessage().'</pre>';
				exit;
			}
			if(empty($user)){
				session_destroy();
				return false;
			}
			$salt = Config::get('admin.auth_key').$_SERVER['HTTP_USER_AGENT'].session_id();
			$hash = Crypter::hash_with_salt($user['secret'], $salt);
			return ($hash === $_SESSION['fingerprint']);
		}
		
		private static function __validate_cookie_fingerprint(){
			if($_COOKIE['PHPSESSID'] !== session_id()) return false;
			try{
				$user = MySQL::table(Config::get('admin.table'))->where('id', '=', $_COOKIE['user_id'])->limit(1)->get('secret');
			}catch(Exception $e){
				echo '<pre>'.$e->getMessage().'</pre>';
				exit;
			}
			if(empty($user)){
				setcookie('logged_in', false, time() - 3600, '/');
				setcookie('user_id', '', time() - 3600, '/');
				setcookie('fingerprint', '', time() - 3600, '/');
				return false;
			}
			$salt = Config::get('admin.auth_key').$_SERVER['HTTP_USER_AGENT'].$_SERVER['REMOTE_ADDR'];
			$hash = Crypter::hash_with_salt($user['secret'], $salt);
			return ($hash === $_COOKIE['fingerprint']);
		}
		
		public static function loggedin(){
			if(isset($_SESSION['logged_in'])){
				return self::__validate_session_fingerprint();
			}elseif(isset($_COOKIE['logged_in'])){
				return self::__validate_cookie_fingerprint();
			}
			return false;
		}
		
		public static function login(){
			if((isset($_POST['submit']) && self::validate()) || self::loggedin()){
				if(isset($_COOKIE['redirect'])){
					$redirect = $_COOKIE['redirect'];
					setcookie('redirect', '', time() - 3600, '/');
				}else{
					$redirect = Config::get('admin.path');
				}
				return Response::redirect($redirect);
			}elseif(isset($_POST['submit'])){
				Flash::redirect('The username or password you entered is not valid.', 'error', array('time' => 5));
				redirect(Config::get('admin.login'));
			}
			$options = array(
				'dir' => Config::get('views.login'),
				'view' => 'login',
				'title' => 'Login',
				'errors' => $errors
			);
			$page = Render::page($options);
			return Response::make($page->render(), $page->status());
		}
		
		public static function logout(){
			Hooks::logout();
			session_destroy();
			setcookie('logged_in', false, time() - 3600, '/');
			setcookie('user_id', '', time() - 3600, '/');
			setcookie('fingerprint', '', time() - 3600, '/');
			redirect('');
		}
		
		private static function validate(){
			$user = $_POST['username'];
			$pass = $_POST['password'];
			// get user info
			try{
				$user_info = MySQL::table(Config::get('admin.table'))->where('username', '=', $user)->limit(1)->get();
			}catch(Exception $e){
				echo '<pre>'.$e->getMessage().'</pre>';
				exit;
			}
			if(empty($user_info)) return false; // no user found
			$hash = $user_info['hash'];
			// check password
			if(Crypter::verify($pass, $hash)){
				// set session vars
				$_SESSION['logged_in'] = true;
				$_SESSION['user_id'] = $user_info['id'];
				$_SESSION['fingerprint'] = Crypter::hash_with_salt($user_info['secret'], Config::get('admin.auth_key').$_SERVER['HTTP_USER_AGENT'].session_id());
				// set cookies
				setcookie('logged_in', true, time() + Config::get('admin.login_time'), '/');
				setcookie('user_id', $user_info['id'], time() + Config::get('admin.login_time'), '/');
				$hash = Crypter::hash_with_salt($user['secret'], Config::get('admin.auth_key').$_SERVER['HTTP_USER_AGENT'].$_SERVER['REMOTE_ADDR']);
				setcookie('fingerprint', $hash, time() + Config::get('admin.login_time'), '/');
				// run user hook
				unset($user_info['hash'], $user_info['secret']);
				Hooks::login($user_info);
				// validated
				return true;
			}else{
				// login not correct
				return false;
			}
		}
		
		public static function validate_user_pass($user, $pass){
			try{
				$user_info = MySQL::table(Config::get('admin.table'))->where('username', '=', $user)->limit(1)->get('hash');
			}catch(Exception $e){
				return false;
			}
			if(empty($user_info)) return false;
			$hash = $user_info['hash'];
			return Crypter::verify($pass, $hash);
		}
		
		public static function validate_pass($pass){
			try{
				$user_info = MySQL::table(Config::get('admin.table'))->where('id', '=', $_SESSION['user_id'])->limit(1)->get('hash');
			}catch(Exception $e){
				return false;
			}
			if(empty($user_info)) return false;
			$hash = $user_info['hash'];
			return Crypter::verify($pass, $hash);
		}
		
		public static function add_user($username, $password, $info = array()){
			$info['username'] = $username;
			// hash the pass
			$info['hash'] = Crypter::hash($password);
			// generate a secret key
			$info['secret'] = uniqid('', true);
			// add to database
			try{
				MySQL::table(Config::get('admin.table'))->insert($info);
				return true;
			}catch(Exception $e){
				return false;
			}
		}
		
		public function dashboard($render = null){
			if(self::loggedin()){
				Hooks::admin();
				if(is_null($render)){
					$request = preg_replace('/^'.Config::get('admin.path').'$/', 'index', App::request());
					$request = preg_replace('/^'.Config::get('admin.path').'\//', '', $request);
					if(empty($request)) $request = 'index';
					$render = array(
						'dir' => Config::get('views.admin'),
						'view' => $request,
						'master' => 'admin'
					);
				}else{
					$defaults = array(
						'dir' => Config::get('views.admin'),
						'master' => 'admin'
					);
					$render = $render + $defaults;
				}
				$page = Render::page($render);
				return Response::make($page->render(), $page->status());
			}else{
				setcookie('redirect', App::request(), time() + 3600, '/');
				redirect(Config::get('admin.login'));
			}
		}
		
		public static function hash_pass($password){
			return Crypter::hash($password);
		}
	
	}