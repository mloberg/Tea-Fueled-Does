<?php namespace TFD\Core\Cache;

	use TFD\Core\Memcached as M;
	use TFD\Core\Config;
	
	class Memcached implements Driver{
	
		public function has($key){
			return (!is_null(self::get(Config::get('cache.key').$key)));
		}
		
		public function get($key){
			return (($cache = M::instance()->get(Config::get('cache.key').$key)) !== false) ? $cache : null;
		}
		
		public function set($key, $value, $time){
			if(Config::get('memcached.class') == 'memcached'){
				M::instance()->set(Config::get('cache.key').$key, $value, $time);
			}else{
				M::instance()->set(Config::get('cache.key').$key, $value, 0, $time);
			}
		}
		
		public function delete($key){
			return M::instance()->delete(Config::get('cache.key').$key);
		}
		
		public function flush(){
			return M::instance()->flush();
		}
	
	}