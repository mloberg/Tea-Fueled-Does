<?php namespace TFD\Core\DB;

	use TFD\Core\Config;
	
	class Connection{
	
		private static $links = array();
		
		function __destruct(){
			foreach(array_keys(self::$links) as $link){
				self::$links[$link] = null;
			}
		}
		
		public function mysql(){
			if(!is_resource(self::$links['mysql']) || !isset(self::$links['mysql'])){
				try{
					self::$links['mysql'] = new \PDO(sprintf('mysql:host=%s;port=%s;dbname=%s', Config::get('mysql.host'), Config::get('mysql.port'), Config::get('mysql.db')), Config::get('mysql.user'), Config::get('mysql.pass'));
					self::$links['mysql']->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
				}catch(\PDOException $e){
					throw new \Exception($e);
				}
			}
			return self::$links['mysql'];
		}
	
	}