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
	
	}
	
	/**
	 * Render a view and a master
	 */
	
	class Page extends Render{
	
		private static $content;
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
					$view = CONTENT_DIR.$options['dir'].'/'.$options['view'].EXT;
				}
			}else{
				$view = WEB_DIR.$options['view'].EXT;
			}
			
			if(!file_exists($view)){
				// 404
			}else{
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
		}
		
		public function __set($name, $value){
			self::$options[$name] = $value;
		}
		
		/**
		 * Getter
		 */
		
		public function __get($name){
			if(array_key_exists($name, self::$options)) return self::$options[$name];
			return null;
		}
		
		/**
		 * Class Methods
		 */
		
		private function bootstrap($options){
			$this->__render_view($options);
			unset($options['view'], $options['dir']);
			$this->set_options($options);
		}
		
		public function master($master){
			$master = MASTERS_DIR.$master.EXT;
			if(!file_exists($master)){
				throw new \TFD\Exception("Master {$master} not found.");
			}
		}
		
		public function replace($text, $replace){
			self::$replace[$text] = $replace;
		}
		
		public function render(){
			return $this->__render_page();
		}
	
	}
	
	/**
	 * Render a view with no master
	 */
	
	class View extends Render{
	
		function __construct($options){
			
		}
		
		function __destruct(){
			
		}
	
	}
	
	/**
	 * Render a partial
	 */
	
	class Partial extends Render{
	
		function __construct($file, $options){
			
		}
	
	}