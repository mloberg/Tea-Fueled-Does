<?php

	class JavaScript{
	
		private static $libraries = array(
			'mootools' => 'js/mootools-core-1.3.1.min.js',
			'mootools-more' => 'js/mootools-more-1.3.1.1.min.js',
			'jquery' => 'https://ajax.googleapis.com/ajax/libs/jquery/1.6.0/jquery.min.js',
			'jquery-ui' => 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.12/jquery-ui.min.js'
		);
		private static $scripts = array();
		private static $script = array();
		private static $ready = array();
		private static $config = array();
		
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
					$ready = "window.onload = (function(){\n";
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
		
		function add_library($name, $src, $load = false){
			self::$libraries[$name] = $src;
		}
		
		function library($lib, $number = null, $load = true){
			if(!array_key_exists($lib, self::$libraries)) return;
			$src = '<script src="'.self::$libraries[$lib].'"></script>';
			if($load && !in_array($src, self::$scripts)){
				if(is_null($number)) $number = count(self::$scripts) + 1;
				self::$scripts[$number] = $src;
			}
			if($lib == 'mootools'){
				self::$config['library'] = 'mootools';
			}elseif($lib == 'jquery'){
				self::$config['library'] = 'jquery';
			}
		}
		
		function load($src, $number = null){
			if(is_array($src)){
				foreach($src as $index => $s){
					self::$scripts[$index] = '<script src="'.$s.'"></script>';
				}
			}else{
				if(is_null($number)) $number = count(self::$scripts) + 1;
				self::$scripts[$number] = '<script src="'.$src.'"></script>';
			}
		}
		
		// functions, vars, etc. that go outside of the domready function
		
		function script($script, $number = null){
			if(is_null($number)) $number = count(self::$script) + 1;
			self::$script[$number] = $script;
		}
		
		// function, vars, etc. that go inside of the domready function
		
		function ready($script, $number = null){
			if(is_null($number)) $number = count(self::$ready) + 1;
			self::$ready[$number] = $script;
		}
	
	}