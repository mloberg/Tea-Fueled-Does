<?php

	class CSS{
	
		private static $sheets = array(
			'reset' => 'css/reset.css'
		);
		private static $styles = array();
		
		function echo_stylesheets(){
			if(is_array(self::$styles)){
				foreach(self::$styles as $s){
					$sheets .= "{$s}\n";
				}
				echo $sheets;
			}
		}
		
		function add_sheet($name, $src){
			self::$sheets[$name] = $src;
		}
		
		function load($src){
			if(array_key_exists($src, self::$sheets)){
				$src = self::$sheets[$src];
			}
			self::$styles[] = '<link rel="stylesheet" href="'.$src.'" />';
		}
	
	}