<?php namespace TFD\DB;

	class MySQL{
	
		static private $connection;
		static private $link;
		static private $info = array();
				
		/**
		 * Accessors
		 */
		
		protected static function set($name, $value){
			self::$info[$name] = $value;
		}
		
		public static function last_query(){
			return self::$info['last_query'];
		}
		
		public static function num_rows(){
			return self::$info['num_rows'];
		}
		
		public static function insert_id(){
			return self::$info['insert_id'];
		}
		
		/**
		 * Class methods
		 */
		
		static private function connection(){
			if(!is_object(self::$connection)){
				self::$connection = new Connection();
			}
			if(!is_object(self::$link)){
				self::$link =& self::$connection->mysql();
			}
			return self::$link;
		}
		
		public static function table($table){
			return new Query($table, self::connection());
		}
	
	}