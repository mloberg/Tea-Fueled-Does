<?php namespace TFD\Core\Cache;

	use TFD\Core\Config;
	
	class APC implements Driver{
	
		public function has($key){
			return (!is_null(this->get(Config::get('cache.key').$key)));
		}
		
		public function get($key){
			return (($cache = apc_fetch(Config::get('cache.key').$key)) !== false) ? $cache : null;
		}
		
		public function set($key, $value, $time){
			apc_store(Config::get('cache.key').$key, $value, $time);
		}
		
		public function delete($key){
			return apc_delete(Config::get('cache.key').$key);
		}
		
		public function flush(){
			return apc_clear_cache('user'); // clear only user cache
		}
		
		public function increase($key, $count = 1){
			return apc_inc($key, $count);
		}
		
		public function decrease($key, $count = 1){
			return apc_dec($key, $count);
		}
	
	}