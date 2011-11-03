<?php namespace TFD\Cache;

	use TFD\Config;
	
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
	
	}