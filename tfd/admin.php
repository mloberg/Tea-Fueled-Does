<?php namespace TFD;

	use TFD\DB\MySQL;
	use Content\Hooks;
	use TFD\Core\Render;
	use TFD\Core\Response;
	use TFD\Config;
	
	class Admin{
	
		private static function __validate_session_fingerprint(){
			$user = MySQL::table(Config::get('admin.table'))->where('id', $_SESSION['user_id'])->limit(1)->get('secret');
			if(empty($user)){
				session_destroy();
				return false;
			}
			$fingerprint = hash('sha1', Config::get('admin.auth_key').$_SERVER['HTTP_USER_AGENT'].session_id().$user['secret']);
			return ($fingerprint === $_SESSION['fingerprint']);
		}
		
		private static function __validate_cookie_fingerprint(){
			if($_COOKIE['PHPSESSID'] !== session_id()) return false;
			$user = MySQL::table(Config::get('admin.table'))->where('id', $_COOKIE['user_id'])->limit(1)->get('secret');
			if(empty($user)){
				setcookie('logged_in', false, time() - 3600, '/');
				setcookie('user_id', '', time() - 3600, '/');
				setcookie('fingerprint', '', time() - 3600, '/');
				return false;
			}
			$fingerprint = hash('sha1', Config::get('admin.auth_key').$_SERVER['HTTP_USER_AGENT'].$_SERVER['REMOTE_ADDR'].$user['secret']);
			return ($fingerprint === $_COOKIE['fingerprint']);
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
				$errors = 'Login incorrect!';
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
			$user_info = MySQL::table(Config::get('admin.table'))->where('username', $user)->limit(1)->get();
			if(empty($user_info)) return false; // no user found
			$salt = $user_info['salt'];
			// check password
			if(AdminValidation::check_password($salt, $pass)){
				// set session vars
				$_SESSION['logged_in'] = true;
				$_SESSION['user_id'] = $user_info['id'];
				$_SESSION['fingerprint'] = hash('sha1', Config::get('admin.auth_key').$_SERVER['HTTP_USER_AGENT'].session_id().$user_info['secret']);
				// set cookies
				setcookie('logged_in', true, time() + Config::get('admin.login_time'), '/');
				setcookie('user_id', $user_info['id'], time() + Config::get('admin.login_time'), '/');
				setcookie('fingerprint', hash('sha1', Config::get('admin.auth_key').$_SERVER['HTTP_USER_AGENT'].$_SERVER['REMOTE_ADDR'].$user_info['secret']), time() + Config::get('admin.login_time'), '/');
				// run user hook
				Hooks::login($user_info);
				// validated
				return true;
			}else{
				// login not correct
				return false;
			}
		}
		
		public static function validate_user_pass($user, $pass){
			$user_info = MySQL::table(Config::get('admin.table'))->where('username', $user)->limit(1)->get('salt');
			if(empty($user_info)) return false;
			$salt = $user_info['salt'];
			return AdminValidation::check_password($salt, $pass);
		}
		
		public static function add_user($username, $password, $info = array()){
			$info['username'] = $username;
			// hash the pass
			$info['salt'] = AdminValidation::hash($password);
			// generate a secret key
			$info['secret'] = uniqid('', true);
			// add to database
			if(MySQL::table(Config::get('admin.table'))->insert($info)){
				return true;
			}
			return false;
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
			return AdminValidation::hash($password);
		}
	
	}
	
	class AdminValidation extends Admin{
	
		private static $algo = '$2a';
		private static $cost = '$10';
		
		private static function unique_salt(){
			return substr(sha1(mt_rand()), 0, 22);
		}
		
		protected static function hash($password){
			return crypt($password, self::$algo.self::$cost.'$'.self::unique_salt());
		}
		
		protected static function check_password($hash, $password){
			$full_salt = substr($hash, 0, 29);
			$new_hash = crypt($password, $full_salt);
			return ($hash == $new_hash);
		}
	
	}