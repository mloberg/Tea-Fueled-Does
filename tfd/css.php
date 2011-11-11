<?php namespace TFD;

	use TFD\Config;
	
	class CSS{
	
		private static $sheets = array();
		private static $styles = null;
		private static $preloaded = array(
			'reset' => 'css/reset.css',
			'jquery-ui' => 'css/ui-lightness/jquery-ui-1.8.14.css'
		);
		
		private static function __prepare($src){
			if(!preg_match('/^http(s*)\:\/\//', $src)) $src = Config::get('site.url').$src;
			return '<link rel="stylesheet" href="'.$src.'" />';
		}
				
		public static function render(){
			$return = '';
			if(is_array(self::$sheets)){
				ksort(self::$sheets);
				foreach(self::$sheets as $s){
					$return .= "\t{$s}\n";
				}
			}
			if(!is_null(self::$styles)){
				$return .= '<style>'.self::$styles."</style>\n";
			}
			return $return;
		}
		
		public static function add_sheet($name, $src){
			self::$sheets[$name] = $src;
		}
		
		public static function load($src, $order = null){
			if(is_array($src)){
				ksort($src);
				foreach($src as $index => $style){
					$o = $index + $order;
					if(isset(self::$sheets[$o])) $o = @max(array_keys(self::$sheets)) + 1;
					$style = self::__prepare($style);
					if(!in_array($style, self::$sheets)) self::$sheets[$o] = $style;
				}
			}else{
				if(isset(self::$preloaded[$src])) $src = self::$preloaded[$src];
				if(is_null($order) && empty(self::$sheets)){
					$order = 0;
				}elseif(is_null($order) || isset(self::$sheets[$order])){
					$order = @max(array_keys(self::$sheets)) + 1;
				}
				
				$src = self::__prepare($src);
				if(!in_array($src, self::$sheets)) self::$sheets[$order] = $src;
			}
		}
		
		public static function style($styles){
			foreach($styles as $element => $style){
				$sheet .= $element.'{';
				foreach($style as $key => $value){
					$sheet .= $key.':'.$value.';';
				}
				$sheet .= '}';
			}
			self::$styles .= $sheet;
		}
	
	}