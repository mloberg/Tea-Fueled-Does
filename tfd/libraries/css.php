<?php

	class CSS{
	
		private static $sheets = array(
			'reset' => 'css/reset.css'
		);
		private static $styles = array();
		private static $style_tag = null;
		
		private static function prepare($src){
			if(!preg_match('/^http(s*)\:\/\//', $src)){
				$src = BASE_URL.$src;
			}
			return '<link rel="stylesheet" href="'.$src.'" />';
		}
		
		function echo_stylesheets(){
			if(is_array(self::$styles)){
				ksort(self::$styles);
				foreach(self::$styles as $s){
					$sheets .= "{$s}\n";
				}
				echo $sheets;
			}
			if(!is_null(self::$style_tag)){
				echo "\t<style>\n".self::$style_tag."\t</style>\n";
			}
		}
		
		function add_sheet($name, $src, $load = false, $order = null){
			self::$sheets[$name] = $src;
			if($load){
				$this->load($name, $order);
			}
		}
		
		function load($src, $order = null){
			if(is_null($order)) $order = count(self::$styles) + 1;
			if(is_array($src)){
				ksort($src);
				foreach($src as $index => $style){
					self::$styles[$index + $order] = self::prepare($style);
				}
			}elseif(array_key_exists($src, self::$sheets)){
				self::$styles[$order] = self::prepare(self::$sheets[$src]);
			}else{
				self::$styles[$order] = self::prepare($src);
			}
		}
		
		function style($styles){
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
			self::$style_tag .= $sheet;
		}
		
		function add_font($name, $src){
			if(is_array($src)){
			
			}
			$style = self::$style_tag;
			$font = <<<FONT
	@font-face{font-family: "{$name}"; src: url('{$src}');}

FONT;
			self::$style_tag = $font.$style;
		}
		
		function flash(){
			$style = self::$style_tag;
			self::$style_tag = <<<FLASH
	#message-flash{
		width: 100%;
		position: relative;
		top: 0;
		left: 0;
		padding: 5px 0;
		text-align: center;
	}
	.message-success{
		background-color: #008000;
		color: #fff;
	}
	.message-error{
		background-color: #b22222;
		color: #fff;
	}
	.message-warning{
		background-color: #ffd700;
		color: #000;
	}
{$style}

FLASH;
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