<?php
	
	class Admin extends App{
	
		protected $login_time;
		
		function __construct(){
			parent::__construct();
			$this->login_time = time() + 3600;
		}
		
		public function loggedin(){
			// check for cookie log in
			if($_COOKIE['sid'] === session_id() && $_SESSION['logged_in'] == true && $_COOKIE[$_SESSION['cid']] = md5($_SESSION['username'].AUTH_KEY)){
				return true;
			}else{
				return false;
			}
		}
		
		public function login(){
			if($_POST['submit'] && $this->validate() || $this->loggedin()){
				if($_SESSION['redirect']){
					// unset cookie
					$redirect = $_SESSION['redirect'];
					unset($_SESSION['redirect']);
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
			setcookie($_SESSION['cid'], '', time() - 3600);
			session_destroy();
			header('Location: ' . BASE_URL);
		}
		
		private function validate(){
			$user = $_POST['username'];
			$pass = $_POST['password'];
			// get user info
			$user_info = $this->mysql->where('username',$user)->limit(1)->get(USERS_TABLE);
			if($user_info == '') return false;
			$salt = $user_info[0]['salt'];
			// check password
			if(AdminValidation::check_password($salt, $pass)){
				// set session vars
				$_SESSION['username'] = $user;
				$_SESSION['logged_in'] = true;
				// set some cookies
				$unique_cookie = uniqid('user_', true);
				setcookie($unique_cookie, md5($user.AUTH_KEY), $this->login_time);
				$_SESSION['cid'] = $unique_cookie;
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
				$_SESSION['redirect'] = $this->request;
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