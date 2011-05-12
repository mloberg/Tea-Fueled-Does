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
		
		function style($styles){
			$sheet = "\t<style>\n";
			foreach($styles as $element => $style){
				$sheet .= <<<STYLE
	{$element}{

STYLE;
				foreach($style as $key => $value){
					if(preg_match('/^(border-radius|drop-shadow)$/', $key)){
						$key = str_replace('-', '_', $key);
						$sheet .= Styles::$key($value);
					}else{
						$sheet .= "\t\t{$key}: {$value};\n";
					}
				}
				$sheet .= "\t}\n";
			}
			$sheet .= "\t</style>";
			self::$styles[] = $sheet;
		}
	
	}
	
	class Styles{
	
		function border_radius($size){
			return <<<CSS
		-moz-border-radius: {$size};
		-webkit-border-radius: {$size};
		border-radius: {$size};

CSS;
		}
		
		function drop_shadow($opts){
			$defaults = array(
				'spread' => '2px',
				'blur' => '5px',
				'color' => '#000'
			);
			$opts = $opts + $defaults;
			return <<<CSS
		box-shadow: {$opts['spread']} {$opts['spread']} {$opts['blur']} {$opts['color']};
		-moz-box-shadow: {$opts['spread']} {$opts['spread']} {$opts['blur']} {$opts['color']};
		-webkit-box-shadow: {$opts['spread']} {$opts['spread']} {$opts['blur']} {$opts['color']};

CSS;
		}
	
	}