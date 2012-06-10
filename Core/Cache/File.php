<?php namespace TFD\Core\Cache;

	use TFD\Core\Config;
	use TFD\Core\File as F;
	
	class File implements Driver{
	
		public function has($key){
			return (!is_null(self::get($key)));
		}
		
		public function get($key){
			if(!file_exists(Config::get('cache.dir').$key)) return null;
			$cache = F::get(Config::get('cache.dir').$key);
			if(time() >= substr($cache, 0, 10)) return self::delete($key);
			return unserialize(substr($cache, 10));
		}
		
		public function set($key, $value, $time){
			F::put(Config::get('cache.dir').$key, (time() + $time).serialize($value));
		}
		
		public function delete($key){
			@unlink(Config::get('cache.dir').$key);
		}
		
		public function flush(){
			foreach(glob(Config::get('cache.dir').'*') as $file){
				@unlink($file);
			}
			return true;
		}
	
	}