<?php namespace TFD\DB;

	class MySQL implements Database{
	
		private static $connection;
		private static $info = array();
		
		private static $table;
		private static $params = array();
		private static $query = array();
		
		function __construct(){
			if(!is_object(self::$connection)){
				self::create_connection();
			}
		}
		
		function __destruct(){
			self::$connection = null;
		}
		
		public static function __callStatic($name, $arguments){
			if(!is_object(self::$connection) && $name !== 'table'){
				self::create_connection();
			}
		}
		
		private static function create_connection(){
			if(!is_object(self::$connection)){
				$con = new Connection();
				try{
					self::$connection = $con->mysql();
				}catch(Exception $e){
					throw new \TFD\Exception($e);
				}
			}
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
			// clear placeholders
			self::$params = array();
			// set table
			self::$table = $table;
			// return new MySQL object
			return new self();
		}
		
		/**
		 * Run a raw SQL statement
		 */
		
		public static function query($query, $params = array(), $return = false){
			self::create_connection();
			$stmt = self::$connection->prepare($query);
			
			// replace query placeholders for set::last_query
			foreach($params as $param => $value){
				$query = str_replace(':'.$param, $value, $query);
			}
			self::set('last_query', $query);
			
			// run query
			try{
				$stmt->execute($params);
				self::set('num_rows', $stmt->rowCount());
				if($return === true){
					$stmt->setFetchMode(\PDO::FETCH_ASSOC);
					$data = array();
					while($row = $stmt->fetch()){
						// if limit one, just return the single row
						if(preg_match('/LIMIT 1/', self::last_query())){
							return $row;
						}
						$data[] = $row;
					}
					return $data;
				}
				return true;
			}catch(\PDOException $e){
				throw new \TFD\Exception($e);
				return false;
			}
		}
		
		/**
		 * Return a PDO Object
		 */
		
		public static function connection(){
			self::create_connection();
			return self::$connection;
		}
		
		/**
		 * SQL setters
		 */
		
		private function __where($fields, $bitwise_operator, $value, $type = 'AND'){
			if(!is_array($fields)) $fields = array($fields => $value);
			foreach($fields as $col => $value){
				// make sure we don't have two placeholders with the same name
				$i = 1;$orig = $col;
				while(isset(self::$params[$col])){
					$col = $orig.$i;
					$i++;
				}
				if(empty(self::$query['where'])){
					self::$query['where'] = sprintf("`%s` %s :%s", $orig, $bitwise_operator, $col);
				}else{
					self::$query['where'] .= sprintf(" %s `%s` %s :%s", $type, $orig, $bitwise_operator, $col);
				}
				self::$params[$col] = $value;
			}
		}
		
		public function where($field, $bitwise_operator = '=', $equal = null){
			self::__where($field, $bitwise_operator, $equal);
			return $this;
		}
		
		public function and_where($field, $bitwise_operator = '=', $equal = null){
			self::__where($field, $bitwise_operator, $equal);
			return $this;
		}
		
		public function or_where($field, $bitwise_operator = '=', $equal = null){
			self::__where($field, $bitwise_operator, $equal, 'OR');
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
			foreach($field as $by => $t){
				if(is_int($by) && !preg_match('/desc|asc/', strtolower($t))){
					$by = $t;
					$t = $type;
				}
				$order .= sprintf("`%s` %s, ", $by, $t);
			}
			self::$query['order'] = $order;
			return $this;
		}
		
		/**
		 * Our query builder helper method. Returns a PDO statement object
		 */
		
		private function query_builder($query){
			// append query helpers to the query
			if(!empty(self::$query['where'])){
				$query .= ' WHERE '.self::$query['where'];
			}
			if(!empty(self::$query['order'])){
				$query .= ' ORDER BY '.substr(self::$query['order'], 0, -2);
			}
			if(!empty(self::$query['limit'])){
				$query .= ' LIMIT '.self::$query['limit'];
			}
			
			// clear query helper info
			self::$query = array();
			
			// create a PDO statement object
			$stmt = self::$connection->prepare($query);
			
			// set the last query
			foreach(self::$params as $param => $value){
				$query = str_replace(':'.$param, '\''.$value.'\'', $query);
				$stmt->bindParam(':'.$param, $value);
			}
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
				$stmt->execute(self::$params);
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
					$i = 0;$orig = $field;
					while(isset(self::$params[$field])){
						$field = $orig.$i;
						$i++;
					}
					$fields .= sprintf("`%s`, ", $orig);
					$values .= sprintf(":%s, ", $field);
					self::$params[$field] = $value;
				}
				$qry = sprintf("INSERT INTO %s (%s) VALUES (%s)", self::$table, substr($fields, 0, -2), substr($values, 0, -2));
				$stmt = self::query_builder($qry);
				try{
					$stmt->execute(self::$params);
					self::set('insert_id', self::$connection->lastInsertId());
					self::set('num_rows', $stmt->rowCount());
					return true;
				}catch(\PDOException $e){
					throw new \TFD\Exception($e);
					return false;
				}
			}
		}
		
		public function update($data, $where = null){
			if(is_array($where)) self::where($where);
			if(!is_array($data)){
				$type = gettype($data);
				throw new \LogicException("MySQL::update expects an array, {$type} given.");
			}elseif(empty(self::$query['where']) && $where !== true){
				throw new \TFD\Exception('WHERE is not set in your query, this will update all rows. Skipping query.');
				return false;
			}else{
				foreach($data as $field => $value){
					$i = 0;$orig = $field;
					while(isset(self::$params[$field])){
						$field = $orig.$i;
						$i++;
					}
					$update .= sprintf("`%s` = :%s, ", $orig, $field);
					self::$params[$field] = $value;
				}
				$qry = sprintf("UPDATE %s SET %s", self::$table, substr($update, 0, -2));
				$stmt = self::query_builder($qry);
				try{
					$stmt->execute(self::$params);
					self::set('num_rows', $stmt->rowCount());
					return true;
				}catch(\PDOException $e){
					throw new \TFD\Exception($e);
					return false;
				}
			}
		}
		
		public function delete($where = null){
			if(is_array($where)) $this->where($where);
			if(empty(self::$query['where']) && $where !== true){
				throw new \TFD\Exception('WHERE is not set in your query, this will delete all rows. Skipping query.');
				return false;
			}else{
				$qry = sprintf("DELETE FROM %s", self::$table);
				$stmt = self::query_builder($qry);
				try{
					$stmt->execute(self::$params);
					self::set('num_rows', $stmt->rowCount());
					return true;
				}catch(\PDOException $e){
					throw new \TFD\Exception($e);
					return false;
				}
			}
		}
	
	}