<?php namespace TFD;

	use TFD\Cache\File;
	use TFD\Cache\APC;
	use TFD\Cache\Memcached;
	
	class Cache{
	
		private static $drivers = array();
		
		public static function driver($driver = null){
			if(is_null($driver)) $driver = Config::get('cache.driver');
			if(!array_key_exists($driver, self::$drivers)){
				switch($driver){
					case 'file':
						return self::$drivers[$driver] = new File();
					case 'memcached':
						return self::$drivers[$driver] = new Memcached();
					case 'apc':
						return self::$drivers[$driver] = new APC();
					default:
						throw new \Exception("Cache driver {$driver} is not supported");
				}
			}
			return self::$drivers[$driver];
		}
		
		/**
		 * Check for existance of a key in the cache
		 */
		
		public static function has($key, $driver = null){
			return self::driver($driver)->has($key);
		}
		
		/**
		 * Get a key from the cache
		 */
		
		public static function get($key, $default = null, $driver = null){
			if(!is_null($item = self::driver($driver)->get($key))) return $item;
			return is_callable($default) ? call_user_func($default) : $default;
		}
		
		/**
		 * Store an item in the cache
		 */
		
		public static function store($key, $value, $time, $driver = null){
			$value = is_callable($value) ? call_user_func($value) : $value;
			self::driver($driver)->set($key, $value, $time);
			return $value;
		}
		
		/**
		 * If an item exists, return it, otherwise store a value
		 */
		
		public static function remember($key, $default, $time, $driver = null){
			if(!is_null($item = self::driver($driver)->get($key))) return $item;
			return self::store($key, $default, $time, $driver);
		}
		
		/**
		 * Remove an item from the cache
		 */
		
		public static function clear($key, $driver = null){
			return self::driver($driver)->delete($key);
		}
		
		/**
		 * Clear all cache items
		 */
		
		public static function clear_all($driver = null){
			return self::driver($driver)->flush();
		}
		
		/**
		 * This helps us create a broader API without having to a lot of extra methods
		 * So instead of doing Cache::driver()->foo(), you can do Cache::foo()
		 */
		
		public static function __callStatic($method, $parameters){
			return call_user_func_array(array(self::driver(), $method), $parameters);
		}
	
	}