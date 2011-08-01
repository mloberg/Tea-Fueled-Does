<?php
	
	class Admin extends App{
	
		protected $login_time;
		
		function __construct(){
			parent::__construct();
			$this->login_time = time() + LOGIN_TIME;
		}
		
		public function loggedin(){
			if($_SESSION['logged_in'] && $this->validate_session_fingerprint()){
				return true;
			}elseif($_COOKIE['loggedin'] && $this->validate_cookie_fingerprint()){
				return true;
			}else{
				return false;
			}
			if($_COOKIE['sid'] === session_id() && $_SESSION['logged_in'] == true && $_COOKIE[$_SESSION['cid']] = md5($_SESSION['username'].AUTH_KEY)){
				return true;
			}else{
				return false;
			}
		}
		
		private function validate_session_fingerprint(){
			$fingerprint = md5(AUTH_KEY.$_SERVER['HTTP_USER_AGENT'].session_id());
			return ($fingerprint === $_SESSION['fingerprint']);
		}
		
		private function validate_cookie_fingerprint(){
			$secret = $this->mysql->where('id', $_COOKIE['uid'])->limit(1)->get(USERS_TABLE, 'secret');
			if(empty($secret)) return false;
			$fingerprint = hash('sha1', AUTH_KEY.$_SERVER['HTTP_USER_AGENT'].$secret[0]['secret']);
			if($fingerprint === $_COOKIE['fingerprint']){
				$_SESSION['logged_in'] = true;
				$_SESSION['fingerprint'] = md5(AUTH_KEY.$_SERVER['HTTP_USER_AGENT'].session_id());
				return true;
			}else{
				return false;
			}
		}
		
		public function login(){
			if($_POST['submit'] && $this->validate() || $this->loggedin()){
				if($_COOKIE['redirect']){
					// unset cookie
					$redirect = $_COOKIE['redirect'];
					setcookie('redirect', '', time() - 3600, '/');
					// redirect
					header('Location: '.BASE_URL.$redirect);
					exit;
				}else{
					header('Location: '. BASE_URL . ADMIN_PATH);
					exit;
				}
			}else{
				if($_POST['submit']){
					$errors = 'Login incorrect!';
				}
				$options = array(
					'dir' => 'admin-www',
					'file' => 'login',
					'title' => 'Login',
					'errors' => $errors
				);
				return $this->render($options);
			}
		}
		
		public function logout(){
			$this->hooks->logout();
			setcookie('loggedin', '', time() - 3600, '/');
			setcookie('fingerprint', '', time() - 3600, '/');
			setcookie('uid', '', time() - 3600, '/');
			session_destroy();
			header('Location: ' . BASE_URL);
		}
		
		private function validate(){
			$user = $_POST['username'];
			$pass = $_POST['password'];
			// get user info
			$user_info = $this->mysql->where('username', $user)->limit(1)->get(USERS_TABLE);
			if(empty($user_info)) return false;
			$salt = $user_info[0]['salt'];
			// check password
			if(AdminValidation::check_password($salt, $pass)){
				// set session vars
				$_SESSION['logged_in'] = true;
				$_SESSION['fingerprint'] = md5(AUTH_KEY.$_SERVER['HTTP_USER_AGENT'].session_id());
				// set cookie
				setcookie('loggedin', true, $this->login_time);
				setcookie('fingerprint', hash('sha1', AUTH_KEY.$_SERVER['HTTP_USER_AGENT'].$user_info[0]['secret']), $this->login_time);
				setcookie('uid', $user_info[0]['id'], $this->login_time);
				// run user hook
				$this->hooks->login($user_info[0]);
				// validated
				return true;
			}else{
				// login not correct
				return false;
			}
		}
		
		protected function add_user($username, $password, $info = array()){
			$info['username'] = $username;
			// hash the pass
			$info['salt'] = AdminValidation::hash($password);
			// generate a secret key
			$info['secret'] = uniqid('', true);
			// add to database
			if($this->mysql->insert(USERS_TABLE, $info)){
				return true;
			}
			return false;
		}
		
		public function dashboard($render=null){
			if($this->loggedin()){
				$this->hooks->admin();
				if(is_null($render)){
					$request = preg_replace('/^'.ADMIN_PATH.'$/', 'index', $this->request);
					$request = preg_replace('/^'.ADMIN_PATH.'\//', '', $request);
					if($request == '') $request = 'index';
					$options = array(
						'dir' => 'admin-dashboard',
						'file' => $request,
						'master' => 'admin'
					);
					return $this->render($options);
				}else{
					if(empty($render['dir'])) $render['dir'] = 'admin-dashboard';
					if(empty($render['master'])) $render['master'] = 'admin';
					return $this->render($render);
				}
			}else{
				setcookie('redirect', $this->request, time() + 3600, '/');
				header('Location: '.BASE_URL.LOGIN_PATH);
			}
		}
		
		public function hash_pass($password){
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