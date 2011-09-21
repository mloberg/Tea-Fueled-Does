<?php namespace TFD\DB;

	class Query{
	
		private static $connection;
		private $table;
		private $builder = array();
		
		function __construct($table, $connection){
			$this->table = $table;
			self::$connection = $connection;
		}
		
		function __destruct(){
			self::$connection = null;
		}
		
		public function get($fields = '*'){
			if(is_array($fields)){
				foreach($fields as $f){
					$tmp .= "`{$f}`,";
				}
				$fields = substr($tmp, 0, -1);
			}
			$qry = sprintf("SELECT %s FROM %s", $fields, $this->table);
			
			$stmt = self::$connection->query($qry);
			$stmt->setFetchMode(\PDO::FETCH_ASSOC);
			while($row = $stmt->fetch()){
				print_p($row);
			}
		}
	
	}