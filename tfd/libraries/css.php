<?php

	class CSS{
	
		private static $sheets = array(
			'reset' => 'css/reset.css',
			'jquery-ui' => 'css/ui-lightness/jquery-ui-1.8.14.css'
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
				echo '<style>'.self::$style_tag."</style>\n";
			}
		}
		
		function add_sheet($name, $src, $load = false, $order = null){
			self::$sheets[$name] = $src;
			if($load){
				$this->load($name, $order);
			}
		}
		
		function load($src, $order = null){
			if(is_array($src)){
				ksort($src);
				foreach($src as $index => $style){
					$o = $index + $order;
					if(isset(self::$styles[$o])) $o = @max(array_keys(self::$styles)) + 1;
					$style = self::prepare($style);
					if(!in_array($style, self::$styles)) self::$styles[] = $style;
				}
			}else{
				if(is_null($order) || isset(self::$styles[$order])) $order = @max(array_keys(self::$styles)) + 1;
				if(array_key_exists($src, self::$sheets)){
					$src = self::prepare(self::$sheets[$src]);
					if(!in_array($src, self::$styles)) self::$styles[$order] = $src;
				}else{
					$src = self::prepare($src);
					if(!in_array($src, self::$styles)) self::$styles[$order] = $src;
				}
			}
		}
		
		function style($styles){
			foreach($styles as $element => $style){
				$sheet .= $element.'{';
				foreach($style as $key => $value){
					if(preg_match('/^(border-radius|drop-shadow)$/', $key)){
						$key = str_replace('-', '_', $key);
						$sheet .= Styles::$key($value);
					}else{
						$sheet .= $key.':'.$value.';';
					}
				}
				$sheet .= '}';
			}
			self::$style_tag .= $sheet;
		}
		
		function add_font($name, $src){
			if(is_array($src)){
			
			}
			$style = self::$style_tag;
			$font = '@font-face{font-family:"'.$name.'";src:url("'.$src.'");}';
			self::$style_tag = $font.$style;
		}
	
	}
	
	class Styles{
	
		function border_radius($size){
			return '-moz-border-radius:'.$size.';-webkit-border-radius:'.$size.';border-radius:'.$size.';';
		}
		
		function drop_shadow($opts){
			$defaults = array(
				'spread' => '2px',
				'blur' => '5px',
				'color' => '#000'
			);
			$opts = $opts + $defaults;
			return 'box-shadow:'.$opts['spread'].' '.$opts['spread'].' '.$opts['blur'].' '.$opts['color'].';-moz-box-shadow:'.$opts['spread'].' '.$opts['spread'].' '.$opts['blur'].' '.$opts['color'].';-webkit-box-shadow:'.$opts['spread'].' '.$opts['spread'].' '.$opts['blur'].' '.$opts['color'];
		}
	
	}