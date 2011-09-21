<?php namespace TFD;
	
	use \Content\Hooks;
	use \TFD\Core\Request;
	use \TFD\Core\Router;
	use \TFD\Core\Render;
	
	class Exception extends \Exception{}
	
	class App{
	
		private static $request;
		
		/**
		 * Magic Methods
		 */
		
		function __construct(){
			session_start();
			$this->bootstrap($_GET['tfd_request']);
		}
		
		function __destruct(){
			
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
			return (string)self::$request;
		}
		
		/**
		 * Class methods
		 */
		
		private function bootstrap($request){
			Hooks::spinup();
			self::$request = new Request($request);
		}
		
		protected function load($name){
/*
			if($name == 'ajax'){
				include_once(AJAX_DIR.'ajax'.EXT);
				return true;
			}
*/
			if(file_exists(HELPER_DIR.$name.EXT)){
				include_once(HELPER_DIR.$name.EXT);
				return true;
			}
			return false;
		}
		
		public function site(){
			Hooks::pre_render();
			$do = self::$request->run();
			if($do !== false){
				return $do;
			}
			
			if(!empty($_SESSION['flash']['message'])){
				$options = (empty($_SESSION['flash']['options']) || !is_array($_SESSION['flash']['options'])) ? array() : $_SESSION['flash']['options'];
				$this->flash->message($_SESSION['flash']['message'], $_SESSION['flash']['type'], $options);
				unset($_SESSION['flash']);
			}
			
			$router = new Router($this->request()); // create a router object
			$route = $router->get();
			
			if(is_array($route)){
				if(($route['logged_in'] || $route['admin']) && !Admin::loggedin()){
					// need to login
					setcookie('redirect', $this->request(), time() + 3600);
					redirect(LOGIN_PATH);
					exit;
				}
				if($route['admin']){
					return Admin::dashboard($route);
				}
				$render_info = $route;
			}else{
				$render_info = array('file' => $this->request());
			}
			Hooks::www();
			Hooks::render();
			$render = new Render($render_info);
			Hooks::post_render();
			return $render;
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