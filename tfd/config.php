<?php namespace TFD;

	/**
	 * Store our config options.
	 *
	 * Config items are stored like such: category.item
	 *   So our application url would be: application.url
	 *   Our MySQL host would be: mysql.host
	 */
	
	class Config{
	
		private static $keys = array();
		
		public static function set($key, $value){
			if(($key = self::parse($key)) !== false){
				self::$keys[$key] = $value;
			}
		}
		
		public static function load($keys){
			if(!is_array($keys)){
				$type = gettype($keys);
				throw new \LogicException("Config::load() expects an array, {$type} sent.");
			}elseif(!empty($keys)){
				foreach($keys as $key => $value){
					if(($key = self::parse($key)) !== false){
						self::$keys[$key] = $value;
					}
				}
			}
		}
		
		public static function get($key, $default = null){
			if(($key = self::parse($key)) !== false && (array_key_exists($key, self::$keys))){
				return self::$keys[$key];
			}
			return false;
		}
		
		private static function parse($key){
			$segments = explode('.', $key);
			if(count($segments) > 2){
				throw new \Exception('Not a valid config key. Must be in the format of category.item');
				return false;
			}elseif(count($segments) === 1){
				return 'application.'.$segments[0];
			}else{
				return $key;
			}
		}
	
	}