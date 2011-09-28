<?php namespace TFD\Core;

	use TFD\Admin;
	use TFD\App;
	use Content\Hooks;
	
	class Render{
	
		protected function __render($file, $extra = array()){
			ob_start();
			extract($extra);
			include($file);
			$render = ob_get_contents();
			ob_end_clean();
			return $render;
		}
		
		public static function page($options){
			return new Page($options);
		}
		
		public static function view($options){
			return new View($options);
		}
		
		public static function partial($file, $options){
			return new Partial($file, $options);
		}
		
		public static function error($type){
			return new ErrorPage($type);
		}
	
	}
	
	/**
	 * Render a view and a master
	 */
	
	class Page extends Render{
	
		private static $content;
		private static $status = 200;
		private static $replace = array();
		private static $options = array(
			'master' => DEFAULT_MASTER,
			'title' => SITE_TITLE
		);
		
		function __construct($options){
			Hooks::pre_render();
			$this->bootstrap($options);
		}
		
		function __destruct(){
			Hooks::post_render();
		}
		
		function __toString(){
			return $this->render();
		}
		
		private function __render_view($options){
			if(isset($options['dir'])){
				if(($options['dir'] === ADMIN_DIR && Admin::loggedin()) || $options['dir'] !== ADMIN_DIR){
					$view = $options['dir'].'/'.$options['view'].EXT;
					if(!file_exists($view)) $view = CONTENT_DIR.$view;
				}
			}else{
				$view = WEB_DIR.$options['view'].EXT;
			}
						
			if(!file_exists($view)){
				self::$status = 404;
			}else{
				unset($options['view']);
				self::$content = parent::__render($view, $options);
			}
		}
		
		private function __content(){
			return str_replace(array_keys(self::$replace), array_values(self::$replace), self::$content)."\n";
		}
		
		private function __render_page(){
			Hooks::render();
			$master = self::$options['master'];
			if(!file_exists($master)){
				throw new \TFD\Exception("The master {$options['master']} doesn't exist!");
				return '';
			}
			
			unset(self::$options['master']);
			self::$options['content'] = self::__content();
			
			$page = parent::__render($master, self::$options);
			
			return $page;
		}
		
		/**
		 * Setters
		 */
		
		public function set_options($options){
			self::$options = $options + self::$options;
			return $this;
		}
		
		public function __set($name, $value){
			self::$options[$name] = $value;
		}
		
		public function set_status($code){
			self::$status = $code;
			return $this;
		}
		
		public function master($master){
			$master = MASTERS_DIR.$master.EXT;
			if(!file_exists($master)){
				throw new \TFD\Exception("Master {$master} not found.");
			}else{
				self::$options['master'] = $master;
			}
			return $this;
		}
		
		/**
		 * Getter
		 */
		
		public function __get($name){
			if(array_key_exists($name, self::$options)) return self::$options[$name];
			return null;
		}
		
		public function status(){
			return self::$status;
		}
		
		/**
		 * Class Methods
		 */
		
		private function bootstrap($options){
			$this->__render_view($options);
			unset($options['view'], $options['dir']);
			if(isset($options['master'])) $options['master'] = MASTERS_DIR.$options['master'].EXT;
			$this->set_options($options);
		}
		
		public function replace($text, $replace = null){
			if(is_array($text)){
				self::$replace = $text + self::$replace;
			}elseif(is_null($replace)){
				throw new \LogicException('Render::replace() expects two parameters, one given.');
			}else{
				self::$replace[$text] = $replace;
			}
			return $this;
		}
		
		public function render(){
			return $this->__render_page();
		}
	
	}
	
	/**
	 * Render a view with no master
	 */
	
	class View extends Render{
	
		private static $options = array();
		
		function __construct($options){
			$this->bootstrap($options);
		}
		
		function __toString(){
			return $this->render();
		}
		
		private function __render_view(){
			if(isset(self::$options['dir'])){
				if((self::$options['dir'] === ADMIN_DIR && Admin::loggedin()) || self::$options['dir'] !== ADMIN_DIR){
					$view = CONTENT_DIR.self::$options['dir'].'/'.self::$options['view'].EXT;
				}
			}else{
				$view = WEB_DIR.self::$options['view'].EXT;
			}
			
			if(!file_exists($view)){
				// 404
			}else{
				unset(self::$options['view']);
				return parent::__render($view, self::$options);
			}
		}
		
		private function bootstrap($options){
			$this->set_options($options);
		}
		
		public function set_options($options){
			self::$options = $options + self::$options;
			return $this;
		}
		
		public function render(){
			return $this->__render_view();
		}
	
	}
	
	/**
	 * Render a partial
	 */
	
	class Partial extends Render{
	
		function __construct($file, $options){
			Hooks::partial();
			$options['view'] = $file;
			return new View($options);
		}
	
	}
	
	class ErrorPage extends Render{
	
		private static $page;
		
		function __construct($type){
			$this->bootstrap($type);
		}
		
		function __toString(){
			return $this->render();
		}
		
		private function __render_page(){
			return parent::__render(self::$page);
		}
		
		private function bootstrap($type){
			self::$page = ERROR_PAGES.$type.EXT;
		}
		
		public function render(){
			return $this->__render_page();
		}
	
	}