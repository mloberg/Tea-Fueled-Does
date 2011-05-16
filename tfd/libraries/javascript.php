<?php

	class JavaScript{
	
		private static $libraries = array(
			'mootools' => 'js/mootools-core-1.3.1.min.js',
			'mootools-more' => 'js/mootools-more-1.3.1.1.min.js',
			'jquery' => 'https://ajax.googleapis.com/ajax/libs/jquery/1.6.0/jquery.min.js',
			'jquery-ui' => 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.12/jquery-ui.min.js'
		);
		private static $scripts = array();
		
		function echo_scripts(){
			if(is_array(self::$scripts)){
				foreach(self::$scripts as $s){
					$scripts .= "{$s}\n";
				}
				echo $scripts;
			}
		}
		
		function add_library($name, $src, $load = false){
			self::$libraries[$name] = $src;
		}
		
		function library($lib){
			$src = self::$libraries[$lib];
			self::$scripts[] = '<script src="'.$src.'"></script>';
		}
		
		function load($src){
			self::$scripts[] = '<script src="'.$src.'"></script>';
		}
		
		function script($script){
			self::$scripts[] = $script;
		}
	
	}