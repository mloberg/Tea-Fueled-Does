<?php namespace TFD\Core;

	use TFD\Admin;
	use TFD\App;
	use Content\Hooks;
	use TFD\Template;
	use TFD\Config;
	
	class Render{
	
		protected function __render($file, $extra = array()){
			ob_start();
			extract($extra, EXTR_SKIP);
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
		
		public static function partial($file, $options = array()){
			Hooks::partial();
			$options['dir'] = Config::get('views.partials');
			$options['view'] = $file;
			return new View($options);
		}
		
		public static function error($type, $data = array()){
			return new ErrorPage($type, $data);
		}
		
		public static function template($template = null, $options = null, $partials = null){
			return new Template($template, $options, $partials);
		}
	
	}
	
	/**
	 * Render a view and a master
	 */
	
	class Page extends Render{
	
		private static $content;
		private static $status = 200;
		private static $replace = array();
		private static $options = array();
		
		function __construct($options){
			$default = array('title' => Config::get('site.title'));
			$options = $options + $default;
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
				if(($options['dir'] === Config::get('views.admin') && Admin::loggedin()) || $options['dir'] !== Config::get('views.admin')){
					$view = VIEWS_DIR.$options['dir'].'/'.$options['view'].EXT;
				}
			}else{
				$view = VIEWS_DIR.Config::get('views.public').'/'.$options['view'].EXT;
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
			if(self::$status != 200 && empty(self::$content)){ // if the status is not 200 and content is empty, send error page
				return Render::error(self::$status)->render();
			}else{
				if(!isset(self::$options['master'])){
					$master = Config::get('render.default_master');
				}else{
					$master = self::$options['master'];
				}
				unset(self::$options['master']);
				
				self::$options['content'] = self::__content();
				
				if($master === false){
					return self::$options['content'];
				}
				
				return parent::__render($master, self::$options);
			}
		}
		
		/**
		 * Setters
		 */
		
		public function set_options($options){
			self::$options = $options + self::$options;
			return $this;
		}
		
		public function __set($name, $value){
			if($name == 'master'){
				$this->master($value);
			}elseif($name == 'status'){
				$this->set_status($value);
			}else{
				self::$options[$name] = $value;
			}
		}
		
		public function set_status($code){
			self::$status = $code;
			return $this;
		}
		
		public function master($master){
			if($master === null) $master = false;
			if($master !== false){
				$master = MASTERS_DIR.$master.EXT;
				if(!file_exists($master)){
					throw new \Exception("Master {$master} not found.");
				}
			}
			self::$options['master'] = $master;
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
			if(isset($options['master']) && ($options['master'] !== null || $options['master'] !== false)){
				$options['master'] = MASTERS_DIR.$options['master'].EXT;
			}
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
				if((self::$options['dir'] === Config::get('views.admin') && Admin::loggedin()) || self::$options['dir'] !== Config::get('views.admin')){
					$view = VIEWS_DIR.self::$options['dir'].'/'.self::$options['view'].EXT;
				}
			}else{
				$view = VIEWS_DIR.Config::get('views.public').'/'.self::$options['view'].EXT;
			}
			
			if(!file_exists($view)){
				throw new \Exception("Could not find view {$view}.");
				return '';
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
	
	class ErrorPage extends Render{
	
		private static $page;
		private static $data;
		
		function __construct($type, $data = array()){
			$this->bootstrap($type, $data);
		}
		
		function __toString(){
			return $this->render();
		}
		
		private function __render_page(){
			return parent::__render(self::$page, self::$data);
		}
		
		private function bootstrap($type, $data){
			self::$page = VIEWS_DIR.Config::get('views.error').'/'.$type.EXT;
			self::$data = $data;
		}
		
		public function render(){
			return $this->__render_page();
		}
	
	}