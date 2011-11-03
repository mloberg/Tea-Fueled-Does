<?php namespace TFD\Cache;

	class APC implements Driver{
	
		public function has($key){
			return (!is_null(this->get($key)));
		}
		
		public function get($key){
			return (($cache = apc_fetch($key)) !== false) ? $cache : null;
		}
		
		public function set($key, $value, $time){
			apc_store($key, $value, $time);
		}
		
		public function delete($key){
			return apc_delete($key);
		}
	
	}