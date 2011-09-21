<?php namespace TFD;
	
	use \Content\Hooks;
	
	class Exception extends \Exception{}
	
	class App{
	
		public $request;
		protected $testing;
		protected $is_admin = false;
		protected $classes = array();
		protected $info = array();
		
		/**
		 * Magic Methods
		 */
		
		function __construct($autoload = ''){
			session_start();
			$this->testing = TESTING_MODE;
			$this->bootstrap($autoload, $_GET['tfd_request']);
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
		
		public static function __autoloader($name){
			global $class_aliases;
			$class = str_replace('\\', '/', $name);
			if(file_exists(BASE_DIR.$class.EXT)){
				include_once(BASE_DIR.$class.EXT);
			}elseif(array_key_exists($name, $class_aliases)){
				$class = str_replace('\\', '/', $class_aliases[$name]);
				include_once(BASE_DIR.$class.EXT);
				class_alias($class_aliases[$name], $name);
			}else{
				throw new Exception("Could not load class {$class}!");
			}
		}
		
		/**
		 * Accessors
		 */
		
		public function request(){
			return (string)$this->request;
		}
		
		/**
		 * Class methods
		 */
		
		private function bootstrap($autoload, $request){
			$this->autoload($autoload);
			$this->request = new Core\Request($request);
		}
		
		private function autoload($autoload){
			if(is_array($autoload)){
				foreach($autoload as $type => $name){
					$this->load($name,$type);
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
			// maintenance mode
			if(MAINTENANCE_MODE){
				ob_start();
				include(MAINTENANCE_PAGE);
				ob_end_flush();
				return;
			}
			Hooks::initialize();
			
			// check for ajax
			if(preg_match('/'.MAGIC_AJAX_PATH.'\/(.*)$/', $this->request())){
				if(empty($_GET['ajax'])){
					$_GET['ajax'] = preg_replace('/^(.*)'.MAGIC_AJAX_PATH.'\//', '', $this->request);
				}
				return $this->ajax->call();
			}elseif(!empty($_SESSION['flash']['message'])){
				$options = (empty($_SESSION['flash']['options']) || !is_array($_SESSION['flash']['options'])) ? array() : $_SESSION['flash']['options'];
				$this->flash->message($_SESSION['flash']['message'], $_SESSION['flash']['type'], $options);
				unset($_SESSION['flash']);
			}
			
			$router = new Core\Router($this->request()); // create a router object
			$route = $router->get();
			
			if(is_array($route)){
/*
				if(($route['logged_in'] || $route['admin']) && !$this->admin->loggedin()){
					exit;
				}
*/
				$render_info = $route;
			}else{
				$render_info = array(
					'file' => $this->request()
				);
			}
			Hooks::render();
			$render = new Core\Render($render_info);
			return $render;
			exit;
			
			
			if(is_array($route)){
				// check for some admin stuff
				if(($route['logged_in'] || $route['admin']) && !$this->admin->loggedin()){
					// redirect to login page and redirect back once logged in
					setcookie('redirect', $this->request, time() + 3600);
					header('Location: '.BASE_URL.LOGIN_PATH);
					exit;
				}
				return $this->render($route);
			}elseif(ADD_USER && $this->request === 'index' && array_key_exists('add_user', $_GET) && $_GET['username'] && $_GET['password']){
				$this->admin->add_user($_GET['username'], $_GET['password']);
				return 'user "'.$_GET['user'].'" added';
			}elseif(preg_match('/^('.preg_quote(ADMIN_PATH).'\/)?logout$/', $this->request)){
				return $this->admin->logout();
			}elseif(preg_match('/^'.preg_quote(LOGIN_PATH).'/', $this->request)){
				return $this->admin->login();
			}elseif(file_exists(WEB_DIR.$this->request.EXT)){
				return $this->render(array('file' => $this->request));
			}elseif(preg_match('/^'.preg_quote(ADMIN_PATH).'/', $this->request)){
				$this->is_admin = true;
				return $this->admin->dashboard();
			}else{
				return $this->render(array('file' => $this->request));
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
			if(!$this->is_admin) Hooks::front();
			Hooks::render();
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
			}elseif($this->testing && $this->request() !== '404'){
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
				return $this->request();
			}else{
				$segments = explode('/', $this->request());
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