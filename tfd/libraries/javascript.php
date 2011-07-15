<?php

	class JavaScript{
	
		private static $libraries = array(
			'mootools' => 'js/mootools-core-1.3.1.min.js',
			'mootools-more' => 'js/mootools-more-1.3.1.1.min.js',
			'jquery' => 'js/jquery-1.6.2.min.js',
			'jquery-ui' => 'js/jquery-ui-1.8.14.min.js'
		);
		private static $scripts = array();
		private static $script = array();
		private static $ready = array();
		private static $config = array();
		
		private static function prepare($src){
			if(!preg_match('/^http(s*)\:\/\//', $src)){
				$src = BASE_URL.$src;
			}
			return '<script src="'.$src.'"></script>';
		}
		
		function echo_scripts(){
			if(!empty(self::$scripts)){
				ksort(self::$scripts);
				foreach(self::$scripts as $s){
					$scripts .= "{$s}\n";
				}
				echo $scripts;
			}
			if(!empty(self::$script) || self::$ready){
				echo "<script>\n";
			}
			if(!empty(self::$script)){
				ksort(self::$script);
				foreach(self::$script as $s){
					$script .= "{$s}\n";
				}
				echo $script;
			}
			if(!empty(self::$ready)){
				ksort(self::$ready);
				if(self::$config['library'] == 'mootools'){
					$ready = "window.addEvent('domready',function(){\n";
				}elseif(self::$config['library'] == 'jquery'){
					$ready = "\$(document).ready(function(){\n";
				}else{
					$ready = "window.onready = (function(){\n";
				}
				foreach(self::$ready as $s){
					$ready .= "{$s}\n";
				}
				$ready .= "});\n";
				echo $ready;
			}
			if(!empty(self::$script) || self::$ready){
				echo "</script>\n";
			}
		}
		
		function add_library($name, $src, $load = false, $order = null){
			self::$libraries[$name] = $src;
			if($load){
				$this->library($name, $order, true);
			}
		}
		
		function library($lib, $load = true, $order = null){
			// if no library, return false
			if(!array_key_exists($lib, self::$libraries)) return false;
			if($load && !in_array($src, self::$scripts)){
				if(is_null($order)) $order = count(self::$scripts) + 1;
				self::$scripts[$order] = self::prepare(self::$libraries[$lib]);;
			}
			if($lib == 'mootools'){
				self::$config['library'] = 'mootools';
			}elseif($lib == 'jquery'){
				self::$config['library'] = 'jquery';
			}
			return true;
		}
		
		function load($src, $order = null){
			if(is_array($src)){
				ksort($src);
				foreach($src as $index => $s){
					self::$scripts[$index + $order] = self::prepare($s);
				}
			}else{
				if(is_null($order)) $order = count(self::$scripts) + 1;
				self::$scripts[$order] = self::prepare($src);
			}
		}
		
		// functions, vars, etc. that go outside of the domready function
		
		function script($script, $order = null){
			if(is_null($order)) $order = count(self::$script) + 1;
			self::$script[$order] = $script;
		}
		
		// function, vars, etc. that go inside of the domready function
		
		function ready($script, $order = null){
			if(is_null($order)) $order = count(self::$ready) + 1;
			self::$ready[$order] = $script;
		}
	
	}