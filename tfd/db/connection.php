<?php namespace TFD\DB;

	class Connection{
	
		private static $links = array();
		
		function __destruct(){
			foreach(array_keys(self::$links) as $link){
				self::$links[$link] = null;
			}
		}
		
		public function mysql(){
			if(!is_resource(self::$links['mysql']) || empty(self::$links['mysql'])){
				try{
					self::$links['mysql'] = new \PDO(sprintf('mysql:host=%s;port=%s;dbname=%s', DB_HOST, DB_PORT, DB), DB_USER, DB_PASS);
				}catch(PDOException $e){
					throw new \Exception($e->getMessage());
				}
			}
			return self::$links['mysql'];
		}
	
	}