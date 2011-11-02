<?php namespace TFD\Cache;

	class APC implements Driver{
	
		public static function has($key){
			return (!is_null(self::get($key)));
		}
		
		public static function get($key){
			return (($cache = apc_fetch($key)) !== false) ? $cache : null;
		}
		
		public static function set($key, $value, $time){
			apc_store($key, $value, $time);
		}
		
		public static function delete($key){
			return apc_delete($key);
		}
	
	}