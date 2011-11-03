<?php namespace TFD\Cache;

	use TFD\Config;
	
	class File implements Driver{
	
		public function has($key){
			return (!is_null(self::get($key)));
		}
		
		public function get($key){
			if(!file_exists(Config::get('cache.dir').$key)) return null;
			$cache = file_get_contents(Config::get('cache.dir').$key);
			if(time() >= substr($cache, 0, 10)) return self::delete($key);
			return unserialize(substr($cache, 10));
		}
		
		public function set($key, $value, $time){
			file_put_contents(Config::get('cache.dir').$key, (time() + $time).serialize($value), LOCK_EX);
		}
		
		public function delete($key){
			@unlink(Config::get('cache.dir').$key);
		}
	
	}