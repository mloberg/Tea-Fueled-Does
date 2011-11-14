<?php namespace TFD;

	use TFD\Config;
	
	class JavaScript{
	
		private static $scripts = array();
		private static $script = array();
		private static $ready = array();
		private static $config = array();
		
		private static $libraries = array(
			'mootools' => 'js/mootools-core-1.4.1-min.js',
			'mootools-more' => 'js/mootools-more-1.4.0.1-min.js',
			'jquery' => 'js/jquery-1.6.4.min.js',
			'jquery-ui' => 'js/jquery-ui-1.8.14.min.js',
			'dojo' => 'http://ajax.googleapis.com/ajax/libs/dojo/1.6.1/dojo/dojo.xd.js'
			'tfd' => 'js/tfd.js'
		);
		
		private static function __prepare($src){
			if(!preg_match('/^http(s*)\:\/\//', $src)){
				$src = Config::get('site.url').$src;
			}
			return '<script src="'.$src.'"></script>';
		}
		
		public static function render(){
			if(empty(self::$config['library']) && !empty(self::$ready)){
				self::library('tfd');
			}
			$return = '';
			if(!empty(self::$scripts)){
				ksort(self::$scripts);
				foreach(self::$scripts as $s){
					$return .= "$s\n";
				}
			}
			if(!empty(self::$script) || self::$ready) $return .= '<script>';
			if(!empty(self::$script)){
				ksort(self::$script);
				foreach(self::$script as $s){
					$return .= $s;
				}
			}
			if(!empty(self::$ready)){
				ksort(self::$ready);
				switch(self::$config['library']){
					case 'mootools':
						$return .= 'window.addEvent("domready",function(){';
						break;
					case 'jquery':
						$return .= '$(document).ready(function(){';
						break;
					case 'tfd':
						$return .= 'window.onDomReady(function(){';
						break;
				}
				foreach(self::$ready as $s){
					$return .= $s;
				}
				$return .= '});';
			}
			if(!empty(self::$script) || self::$ready) $return .= "</script>\n";
			return $return;
		}
		
		public static function add_library($name, $src, $load = false, $order = null){
			self::$libraries[$name] = $src;
			if($load) self::library($name, true, $order);
		}
		
		public static function update_library($name, $src, $load = false, $order = null){
			self::$libraries[$name] = $src;
			if($load) self::library($name, true, $order);
		}
		
		public static function library($lib, $load = true, $order = null){
			if(!array_key_exists($lib, self::$libraries)){
				throw new \Exception("No such JavaScript library, {$lib}");
				return false;
			}
			if($load && !in_array($src, self::$scripts)){
				if(is_null($order) && empty(self::$scripts)){
					$order = 0;
				}elseif(is_null($order) || isset(self::$scripts[$order])){
					$order = @max(array_keys(self::$scripts)) + 1;
				}
				self::$scripts[$order] = self::__prepare(self::$libraries[$lib]);;
			}
			if($lib == 'mootools'){
				self::$config['library'] = 'mootools';
			}elseif($lib == 'jquery'){
				self::$config['library'] = 'jquery';
			}
			return true;
		}
		
		public static function load($src, $order = null){
			if(is_array($src)){
				ksort($src);
				foreach($src as $index => $s){
					$o = $index + $order;
					if(isset(self::$scripts[$o])) $o = @max(array_keys(self::$scripts)) + 1;
					$s = self::__prepare($s);
					if(!in_array($s, self::$scripts)) self::$scripts[$o] = $s;
				}
			}else{
				if(is_null($order) && empty(self::$scripts)){
					$order = 0;
				}elseif(is_null($order) || isset(self::$scripts[$order])){
					$order = @max(array_keys(self::$scripts)) + 1;
				}
				$src = self::__prepare($src);
				if(!in_array($src, self::$scripts)) self::$scripts[$order] = $src;
			}
		}
		
		// functions, vars, etc. that go outside of the domready function
		
		public static function script($script, $order = null){
			if(is_null($order) && empty(self::$script)){
				$order = 0;
			}elseif(is_null($order) || isset(self::$script[$order])){
				$order = @max(array_keys(self::$script)) + 1;
			}
			if(!in_array($script, self::$script)) self::$script[$order] = $script;
		}
		
		// function, vars, etc. that go inside of the domready function
		
		public static function ready($script, $order = null){
			if(is_null($order) && empty(self::$ready)){
				$order = 0;
			}elseif(is_null($order) || isset(self::$ready[$order])){
				$order = @max(array_keys(self::$ready)) + 1;
			}
			if(!in_array($script, self::$ready)) self::$ready[$order] = $script;
		}
	
	}