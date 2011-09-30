<?php namespace TFD\DB;

	class MySQL implements Database{
	
		private static $connection;
		private static $link;
		private static $info = array();
		
		private static $table;
		private static $placeholders = array();
		private static $query = array();
		
		function __construct(){
			if(!is_object(self::$connection)){
				self::create_connection();
			}
		}
		
		function __destruct(){
			self::$connection = null;
		}
		
		public static function __callStatic(){
			if(!is_object(self::$connection)){
				self::create_connection();
			}
		}
		
		private static function create_connection(){
			$con = new Connection();
			self::$connection = $con->mysql();
		}
		
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
		 * Return a new MySQL class
		 */
		
		public static function table($table){
			self::$table = $table;
			return new self();
		}
		
		/**
		 * Run a raw SQL statement
		 */
		
		public static function query($query, $return = false){
			
		}
		
		/**
		 * SQL setters
		 */
		
		private function __where($fields, $value, $type = 'AND'){
			$where = self::$query['where'];
			if(!is_array($field)) $field = array($field => $value);
			foreach($field as $col => $value){
				if(empty($where)){
					$where = sprintf("`%s` = :%s", $col, $col);
				}else{
					$where .= sprintf(" %s `%s` = :%s", $type, $col, $col);
				}
				self::$placeholders[$col] = $value;
			}
			self::$query['where'] = $where;
		}
		
		public function where($field, $equal = null){
			self::__where($field, $equal);
			return $this;
		}
		
		public function and_where($field, $equal = null){
			self::__where($field, $equal);
			return $this;
		}
		
		public function or_where($field, $equal = null){
			self::__where($field, $equal, 'OR');
			return $this;
		}
		
		private function __like($fields, $value, $type = 'AND'){
			$where = self::$query['where'];
			if(!is_array($fields)) $fields = array($fields => $value);
			foreach($fields as $col => $value){
				if(empty($where)){
					$where = sprintf("`%s` LIKE :%s", $col, $col);
				}else{
					$where .= sprintf(" %s `%s` LIKE :%s", $type, $col, $col);
				}
				self::$placeholders[$col] = $value.'%s';
			}
			self::$query['where'] = $where;
		}
		
		public function like($field, $like = null){
			self::__like($field, $like);
			return $this;
		}
		
		public function and_like($field, $like = null){
			self::__like($field, $like);
			return $this;
		}
		
		public function or_like($field, $like = null){
			self::__like($field, $like, 'OR');
			return $this;
		}
		
		public function limit($limit){
			if(!is_int($limit)){
				$type = gettype($limit);
				throw new \LogicException("MySQL::limit() expects an integer, {$type} given.");
			}else{
				self::$query['limit'] = $limit;
			}
			return $this;
		}
		
		public function order_by($field, $type = 'DESC'){
			$order = self::$query['order'];
			if(!is_array($field)) $field = array($field => $type);
			foreach($order_by as $by => $t){
				if(is_int($col) && !preg_match('/desc|asc/', strtolower($t))){
					$by = $t;
					$t = $type;
				}
				$order .= sprintf("`%s` %s, ", $col, $t);
			}
			self::$query['order'] = $order;
			return $this;
		}
		
		/**
		 * Our query builder helper method. Returns a PDO statement object
		 */
		
		private function query_builder($query){
			if(!empty(self::$query['where'])){
				$query .= ' WHERE '.self::$query['where'];
			}
			if(!empty(self::$query['order'])){
				$query .= ' ORDER BY '.substr(self::$query['order'], 0, -2);
			}
			if(!empty(self::$query['limit'])){
				$query .= ' LIMIT '.self::$query['limit'];
			}
			
			$stmt = self::$connection->prepare($query);
			
			foreach(self::$placeholders as $param => $value){
				$query = str_replace(':'.$param, '\''.$value.'\'', $query);
			}
						
			// set last_query
			self::set('last_query', $query);
			
			return $stmt;
		}
		
		/**
		 * Main SQL method
		 */
		
		public function get($fields = '*'){
			if(is_array($fields)){
				foreach($fields as $f){
					$tmp .= "`{$f}`,";
				}
				$fields = substr($tmp, 0, -1);
			}
			$qry = sprintf("SELECT %s FROM %s", $fields, self::$table);
			$stmt = self::query_builder($qry);
			try{
				$stmt->execute(self::$placeholders);
			}catch(\PDOException $e){
				throw new \TFD\Exception($e);
			}
			self::set('num_rows', $stmt->rowCount());
			$stmt->setFetchMode(\PDO::FETCH_ASSOC);
			$data = array();
			while($row = $stmt->fetch()){
				// if limit 1, return the single row
				if(preg_match('/LIMIT 1/', self::last_query())){
					return $row;
				}
				$data[] = $row;
			}
			return $data;
		}
		
		public function insert($data){
			if(!is_array($data)){
				$type = gettype($data);
				throw new \LogicException("MySQL::insert() expects an array, {$type} given.");
			}else{
				foreach($data as $field => $value){
					
				}
			}
		}
		
		public function update($data, $where = null){
		
		}
		
		public function delete($where = null){
			
		}
	
	}
	
	class SQL extends MySQL implements Query{
	
		
		/**
		 * Main Methods
		 */
		
		public function insert($data){
			if(!is_array($data)){
				throw new \LogicException('Query->insert() requires an array.');
			}else{
				foreach($data as $field => $value){
					$fields .= sprintf("`%s`, ", $field);
					$values .= sprintf(':%s, ', $field);
					self::$placeholders[$field] = $value;
				}
				$qry = sprintf("INSERT INTO %s (%s) VALUES (%s)", $this->table, substr($fields, 0, -2), substr($values, 0, -2));
				$stmt = self::query_builder($qry);
				try{
					$stmt->execute(self::$placeholders);
					parent::set('insert_id', self::$connection->lastInsertId());
					return true;
				}catch(\PDOException $e){
					throw new \TFD\Exception($e);
					return false;
				}
			}
		}
		
		public function update($data, $where = null){
			if(is_array($where)) $this->where($where);
			if(!is_array($data)){
				throw new \LogicException('Query->update requires an array!');
			}elseif(empty(self::$info['where']) && $where !== true){
				throw new \TFD\Exception('WHERE is not in your query, this will update all rows. Skipping query.');
			}else{
				foreach($data as $field => $value){
					$update .= sprintf("`%s`=:%s, ", $field, $field);
					self::$placeholders[$field] = $value;
				}
				$qry = sprintf("UPDATE %s SET %s", $this->table, substr($update, 0, -2));
				$stmt = self::query_builder($qry);
				try{
					$stmt->execute(self::$placeholders);
					return true;
				}catch(\PDOException $e){
					throw new \TFD\Exception($e);
					return false;
				}
			}
		}
		
		public function delete($where = null){
			if(is_array($where)) $this->where($where);
			if(empty(self::$info['where']) && $where !== true){
				throw new \TFD\Exception('WHERE is not set in your query, this will delete all rows. Skipping query.');
			}else{
				$qry = sprintf("DELETE FROM %s", $this->table);
				$stmt = self::query_builder($qry);
				try{
					$stmt->execute(self::$placeholders);
					return true;
				}catch(\PDOException $e){
					throw new \TFD\Exception($e);
					return false;
				}
			}
		}
	
	}