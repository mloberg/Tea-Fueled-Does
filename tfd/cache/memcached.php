<?php namespace TFD\Cache;

	use TFD\Memcached as M;
	use TFD\Config;
	
	class Memcached implements Driver{
	
		public static function has($key){
			return (!is_null(self::get($key)));
		}
		
		public static function get($key){
			return (($cache = M::instance()->get($key)) !== false) ? $cache : null;
		}
		
		public static function set($key, $value, $time){
			if(Config::get('memcached.class') == 'memcached'){
				M::instance()->set($key, $value, $time);
			}else{
				M::instance()->set($key, $value, 0, $time);
			}
		}
		
		public static function delete($key){
			return M::instance()->delete($key);
		}
	
	}