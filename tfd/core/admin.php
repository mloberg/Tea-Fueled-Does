<?php
	
	class Admin extends App{
	
		public function loggedin(){
			if($_SESSION['logged_in']){
				return $this->validate_session_fingerprint();
			}elseif($_COOKIE['logged_in']){
				return $this->validate_cookie_fingerprint();
			}else{
				return false;
			}
		}
		
		private function validate_session_fingerprint(){
			$user = $this->mysql->where('id', $_SESSION['user_id'])->limit(1)->get(USERS_TABLE, 'secret');
			if(empty($user)){
				session_destroy();
				return false;
			}
			$fingerprint = hash('sha1', AUTH_KEY.$_SERVER['HTTP_USER_AGENT'].session_id().$user[0]['secret']);
			return ($fingerprint === $_SESSION['fingerprint']);
		}
		
		private function validate_cookie_fingerprint(){
			if($_COOKIE['PHPSESSID'] !== session_id()) return false;
			$user = $this->mysql->where('id', $_COOKIE['user_id'])->limit(1)->get(USERS_TABLE, 'secret');
			if(empty($user)){
				setcookie('logged_in', false, time() - 3600, '/');
				setcookie('user_id', '', time() - 3600, '/');
				setcookie('fingerprint', '', time() - 3600, '/');
				return false;
			}
			$fingerprint = hash('sha1', AUTH_KEY.$_SERVER['HTTP_USER_AGENT'].$_SERVER['REMOTE_ADDR'].$user[0]['secret']);
			return ($fingerprint === $_COOKIE['fingerprint']);
		}
		
		public function login(){
			if($_POST['submit'] && $this->validate() || $this->loggedin()){
				if($_COOKIE['redirect']){
					$redirect = $_COOKIE['redirect'];
					setcookie('redirect', '', time() - 3600, '/');
				}else{
					$redirect = ADMIN_PATH;
				}
				header('Location: '.BASE_URL.$redirect);
				exit;
			}else{
				if($_POST['submit']){
					$errors = 'Login incorrect!';
				}
				$options = array(
					'dir' => LOGIN_DIR,
					'file' => 'login',
					'title' => 'Login',
					'errors' => $errors
				);
				return $this->render($options);
			}
		}
		
		public function logout(){
			$this->hooks->logout();
			session_destroy();
			setcookie('logged_in', false, time() - 3600, '/');
			setcookie('user_id', '', time() - 3600, '/');
			setcookie('fingerprint', '', time() - 3600, '/');
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
				$_SESSION['user_id'] = $user_info[0]['id'];
				$_SESSION['fingerprint'] = hash('sha1', AUTH_KEY.$_SERVER['HTTP_USER_AGENT'].session_id().$user_info[0]['secret']);
				// set cookies
				setcookie('logged_in', true, time() + LOGIN_TIME, '/');
				setcookie('user_id', $user_info[0]['id'], time() + LOGIN_TIME, '/');
				setcookie('fingerprint', hash('sha1', AUTH_KEY.$_SERVER['HTTP_USER_AGENT'].$_SERVER['REMOTE_ADDR'].$user_info[0]['secret']), time() + LOGIN_TIME, '/');
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
						'dir' => ADMIN_DIR,
						'file' => $request,
						'master' => 'admin'
					);
					return $this->render($options);
				}else{
					if(empty($render['dir'])) $render['dir'] = ADMIN_DIR;
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