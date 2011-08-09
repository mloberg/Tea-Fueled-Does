<?php

	class App extends TFD{}class TFD{
	
		public $request;
		protected $testing;
		protected $is_admin = false;
		protected $classes = array();
		protected $info = array();
		
		function __construct($autoload=''){
			session_start();
			$this->testing = TESTING_MODE;
			if($_GET['tfd_request'] == ''){
				$this->request = 'index';
			}elseif(preg_match('/\/$/', $_GET['tfd_request'])){
				$this->request = $_GET['tfd_request'].'index';
			}else{
				$this->request = $_GET['tfd_request'];
			}
			if(is_array($autoload)){
				foreach($autoload as $type => $name){
					$this->load($name,$type);
				}
			}
		}
		
		function __destruct(){
			unset($this->classes);
		}
		
		public function __get($name){
			if(array_key_exists($name, $this->classes)){
				return $this->classes[$name];
			}else{
				// load the class
				if($name == 'hooks'){
					include_once(HOOKS_FILE);
					$this->classes[$name] = new Hooks();
					return $this->classes[$name];
				}elseif($this->load($name)){
					// save the class
					if($name == 'mysql'){
						$this->classes[$name] = new Database();
					}else{
						$this->classes[$name] = new $name();
					}
					// return the class
					return $this->classes[$name];
				}else{
					$this->error->report("Could not load class: {$name}",true);
				}
			}
		}
		
		protected function load($name){
			if($name == 'ajax'){
				include_once(AJAX_DIR.'ajax'.EXT);
				return true;
			}
			$dirs = array(CORE_DIR,LIBRARY_DIR,HELPER_DIR,MODELS_DIR);
			for($i=0;$i <= count($dirs);$i++){
				$file = $dirs[$i].$name.EXT;
				if(file_exists($file)){
					include_once($file);
					return true;
					break;
				}elseif($i == count($dirs)){
					return false;
					break;
				}
			}
		}
		
		public function site(){
			if(MAINTENANCE_MODE){
				ob_start();
				include(MAINTENANCE_PAGE);
				ob_end_flush();
				return;
			}
			$this->hooks->initialize();
			if(preg_match('/'.MAGIC_AJAX_PATH.'\/(.*)$/', $this->request)){
				if(empty($_GET['ajax'])){
					$_GET['ajax'] = preg_replace('/^(.*)'.MAGIC_AJAX_PATH.'\//', '', $this->request);
				}
				return $this->ajax->call();
			}elseif(!empty($_SESSION['flash']['message'])){
				$options = (empty($_SESSION['flash']['options']) || !is_array($_SESSION['flash']['options'])) ? array() : $_SESSION['flash']['options'];
				$this->flash->message($_SESSION['flash']['message'], $_SESSION['flash']['type'], $options);
				unset($_SESSION['flash']);
			}
			// get routes
			$route = $this->routes();
			// if a matched route was found, use it
			if(is_array($route)){
				// check for some admin stuff
				if($route['logged_in'] || $route['admin']){
					if(!$this->admin->loggedin()){
						// redirect to login page and redirect back once logged in
						setcookie('redirect', $this->request, time() + 3600);
						header('Location: '.BASE_URL.LOGIN_PATH);
					}
					if($route['admin']) return $this->admin->dashboard($route);
				}
				// check to see if it's a module or redirect
				if($route['redirect']){
					header('Location: '.BASE_URL.$route['redirect']);
					exit;
				}elseif($route['module']){
					return $this->module->load_module($route['module']);
				}
				return $this->render($route);
			}elseif(ADD_USER && $this->request === 'index' && array_key_exists('add_user', $_GET) && $_GET['username'] && $_GET['password']){
				$this->admin->add_user($_GET['username'], $_GET['password']);
				return 'user "'.$_GET['user'].'" added';
			}elseif(preg_match('/^('.ADMIN_PATH.'\/)?logout$/', $this->request)){
				return $this->admin->logout();
			}elseif(preg_match('/^'.LOGIN_PATH.'/', $this->request)){
				return $this->admin->login();
			}elseif(file_exists(WEB_DIR.$this->request.EXT)){
				return $this->render(array('file' => $this->request));
			}elseif(preg_match('/^'.ADMIN_PATH.'/', $this->request)){
				$this->is_admin = true;
				return $this->admin->dashboard();
			}else{
				return $this->render(array('file' => $this->request));
			}
		}
		
		protected function routes(){
			// get the routes file
			$routes_file = file_get_contents(CONTENT_DIR . 'routes.json');
			$routes = json_decode($routes_file, true);
			$routes = $routes['routes'];
			// look for a match and if match, return
			foreach($routes as $key => $val){
				if($key == $this->request){
					$this->info['route'] = $key;
					return $val;
					break;
				}
				$match = str_replace('/', '\/', $key);
				$match = str_replace('[:any]', '([\w\?\.\#_\-\+\*\^\/]+)?', $match);
				$match = str_replace('[:num]', '([0-9]+)', $match);
				if(preg_match("/^{$match}$/", $this->request, $matches)){
					$this->info['route'] = $key;
					if($val['file']){
						$redirect = str_replace('$', '$r_', $val['file']);
						extract($matches, EXTR_PREFIX_ALL, 'r');
						eval("\$redirect = \"$redirect\";");
						$val['file'] = $redirect;
						return $val;
					}elseif($val['redirect']){
						$redirect = str_replace('$', '$r_', $val['redirect']);
						extract($matches, EXTR_PREFIX_ALL, 'r');
						eval("\$redirect = \"$redirect\";");
						$val['redirect'] = $redirect;
						return $val;
					}else{
						return $val;
					}
					break;
				}
			}
		}
		
		function partial($file, $extra=null){
			if(is_array($extra)) extract($extra);
			$file = PARTIALS_DIR.$file.EXT;
			if(file_exists($file)){
				ob_start();
				include($file);
				$partial = ob_get_contents();
				ob_end_clean();
				return $partial;
			}elseif($this->testing){
				$this->error->report("Partial: {$file} doesn't exist");
			}
			return;
		}
		
		protected function render($options){
			if(!$this->is_admin) $this->hooks->front();
			$this->hooks->render();
			extract($options);
			// get full path of the file
			if($dir){
				if($dir == 'admin-dashboard' && !$this->admin->loggedin()){
					$this->send_404();
					$master = '404';
				}else{
					$file = CONTENT_DIR . $dir.'/'.$file.EXT;
				}
			}else{
				$file = WEB_DIR . $file . EXT;
			}
			// start the output buffer
			ob_start();
			if(file_exists($file)){
				// include the file
				include($file);
				// save file contents to var
				$content = ob_get_contents();
				// clean the output buffer
				ob_clean();
			}elseif($this->testing && $this->request !== '404'){
				$this->send_404();
				$this->error->report($file.' not found!');
			}else{
				// if the file wasn't found, 404
				$this->send_404();
				$master = '404';
			}
			// figure out the title
			if(!$options['title'] && $title == ''){
				$title = SITE_TITLE;
			}elseif($options['title']){
				$title = $options['title'];
			}
			if(!empty($replace)){
				foreach($replace as $item => $value){
					$content = str_replace($item, $value, $content);
				}
			}
			// get the full path to the master
			$master = MASTERS_DIR . $master . EXT;
			if(!file_exists($master)){
				// if the master doesn't exist, use the default one
				$master = DEFAULT_MASTER;
			}
			// include master
			include($master);
			// save it to a var
			$page = ob_get_contents();
			// end the output buffer
			ob_end_clean();
			
			// return the page
			return $page;
		}
		
		// General Functions
		
		function send_404(){
			header('HTTP/1.1 404 Not Found');
		}
		
		protected function url($segment = null){
			if($segment == null){
				return $this->request;
			}else{
				$segments = explode('/', $this->request);
				$seg = $segment - 1;
				return $segments[$seg];
			}
		}
		
		function flash($message, $type = 'message', $options = array()){
			$this->flash->message($message, $type, $options);
		}
		
		function profile(){
			return array(
				round(microtime(true) - START_TIME, 4),
				round((memory_get_peak_usage() - START_MEM) / pow(1024, 2), 3)
			);
		}
	
	}