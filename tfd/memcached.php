<?php namespace TFD;

	use TFD\Config;
	
	class Memcached{
	
		private static $instance = null;
		
		public static function set_servers($servers){
			Config::set('memcached.servers', $servers);
			if(!is_null(self::$instance)){
				self::$instance = self::connect(Config::get('memcached.servers'));
			}
		}
		
		public static function add_server($server){
			$current = Config::get('memcached.servers');
			$current[] = $server;
			Config::set('memcached.servers', $current);
			if(!is_null(self::$instance)){
				if(Config::get('memcached.class') == 'memcached'){
					self::$instance->addServer($server['host'], $server['port'], $server['weight']);
				}else{
					self::$instance->addServer($server['host'], $server['port'], true, $server['weight']);
				}
			}
		}
		
		public static function instance(){
			if(is_null(self::$instance)){
				self::$instance = self::connect(Config::get('memcached.servers'));
			}
			return self::$instance;
		}
		
		private static function connect($servers){
			$class = Config::get('memcached.class');
			$class = (empty($class)) ? 'memcache' : $class;
			
			if(!class_exists($class, false)){
				throw new \Exception("The class '{$class}' is not available on this system");
			}elseif($class == 'memcached'){
				$m = new \Memcached();
				$map_func = function($value){ return array_values($value); };
				$servers = array_map($map_func, $servers);
				$m->addServers($servers);
			}else{
				$m = new \Memcache();
				foreach($servers as $server){
					$m->addServer($server['host'], $server['port'], true, $server['weight']);
				}
			}
			
			if($m->getVersion() === false){
				throw new \Exception('No connections could be made');
			}
			
			return $m;
		}
	
	}