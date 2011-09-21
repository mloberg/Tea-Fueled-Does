<?php namespace TFD\Core;

	/**
	 * $page = new Render(array('file' => 'page'));
	 * $page->master('cutom-master');
	 * return $page; // rendered page
	 *
	 * I like this class a lot, it potentially allows you to create seperate pages (but why would you need to?),
	 *  but you run into an issue when trying to access it through your page
	 */

	use \TFD\Admin;
	use \TFD\App;
	
	class Render{
	
		static private $replace = array();
		static private $content;
		static private $options = array(
			'master' => DEFAULT_MASTER,
			'title' => SITE_TITLE
		);
		
		function __construct($options){
			// what file are we rendering?
			if($options['dir'] === ADMIN_DIR && !Admin::loggedin()){
				// 404
				
				$this->master('404');
				return;
			}elseif(isset($options['dir']) && isset($options['file'])){
				$file = "{$options['dir']}/{$options['file']}".EXT;
			}elseif(isset($options['file'])){
				$file = WEB_DIR . $options['file'] . EXT;
			}else{
				$file = WEB_DIR . $this->request . EXT;
			}
						
			// start the output buffer
			ob_start();
			if(file_exists($file)){
				include($file);
				self::$content = ob_get_contents();
			}elseif($this->testing && $this->request !== '404'){
				// 404
				
				$this->master('404');
				// report the error in detail
				
			}else{
				// 404
				
				$this->master('404');
			}
			ob_end_clean();
		}
		
		/**
		 * Setter method
		 */
		
		function __set($name, $value){
			self::$options[$name] = $value;
		}
		
		/**
		 * Getter method
		 */
		
		function __get($name){
			if(array_key_exists($name, self::$options)) return self::$options[$name];
			return null;
		}
		
		/**
		 * This returns our page fully rendered out
		 */
		
		function __toString(){
			// what master are we using?
			$master = self::$options['master'];
			if(!file_exists($master)){
				// the master doesn't exist
			}
			
			// run the replace method
			if(!empty(self::$replace)) self::run_replace();
			
			// get the content
			$content = self::$content;
			
			// get all saved variables to use in the master
			unset(self::$options['master']);
			extract(self::$options);
			
			// start the output buffer
			ob_start();
			include($master);
			$render = ob_get_contents();
			ob_end_clean();
			
			// return the rendered page
			return $render;
		}
		
		function original($options){
			$this->hooks->render();
			extract($options);

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
		
		function master($master){
			self::$options['master'] = MASTERS_DIR.$master.EXT;
		}
		
		function replace($text, $replace){
			self::$replace[$text] = $replace;
		}
		
		private static function run_replace(){
			self::$content = str_replace(array_keys(self::$replace), array_values(self::$replace), self::$content);
		}
	
	}