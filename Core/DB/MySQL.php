<?php namespace TFD\Core\DB;

	class MySQL implements Database{
	
		private static $connection;
		private static $info = array();
		
		private $table;
		private $params = array();
		private $query = array();
		
		function __construct($table = null){
			$this->table = $table;
			if(!is_object(self::$connection)){
				self::create_connection();
			}
		}
		
		private static function create_connection(){
			if(!is_object(self::$connection)){
				$con = new Connection();
				try{
					self::$connection =& $con->mysql();
				}catch(\Exception $e){
					throw new \Exception($e);
				}
			}
		}
		
		private function __params($param, $value){
			$i = 1;
			$original = $param;
			while(isset($this->params[$param])){
				$param = $original.$i;
				$i++;
			}
			$this->params[$param] = $value;
			return $param;
		}
		
		/**
		 * Accessors
		 */
		
		public static function last_query($query = null){
			if(!is_null($query)) self::$info['last_query'] = $query;
			return self::$info['last_query'];
		}
		
		public static function num_rows($num_rows = null){
			if(!is_null($num_rows)) self::$info['num_rows'] = $num_rows;
			return self::$info['num_rows'];
		}
		
		public static function insert_id($id = null){
			if(!is_null($id)) self::$info['insert_id'] = $id;
			return self::$info['insert_id'];
		}
		
		/**
		 * Return a new MySQL class
		 */
		
		public static function table($table){
			// return new MySQL object
			return new self($table);
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
			self::last_query($query);
			
			// run query
			try{
				$stmt->execute($params);
				self::num_rows($stmt->rowCount());
				if($return === true){
					$stmt->setFetchMode(\PDO::FETCH_ASSOC);
					$data = array();
					while($row = $stmt->fetch()){
						// if limit one, just return the single row
						if(preg_match('/LIMIT 1( |\z)/', self::last_query())){
							return $row;
						}
						$data[] = $row;
					}
					return $data;
				}
				return true;
			}catch(\PDOException $e){
				throw new \Exception($e);
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
			// in case they didn't set a bitwise operator
			if(is_null($value) && !is_array($fields)){
				$fields = array($fields => $bitwise_operator);
				$bitwise_operator = '=';
			}elseif(!is_array($fields)){
				$fields = array($fields => $value);
			}
			foreach($fields as $col => $value){
				$param = $this->__params($col, $value);
				if(empty($this->query['where'])){
					$this->query['where'] = sprintf("`%s` %s :%s", $col, $bitwise_operator, $param);
				}else{
					$this->query['where'] .= sprintf(" %s `%s` %s :%s", $type, $col, $bitwise_operator, $param);
				}
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
				throw new \LogicException("MySQL::limit() expects an integer, {$type} given");
			}else{
				$this->query['limit'] = $limit;
			}
			return $this;
		}
		
		public function order_by($field, $type = 'DESC'){
			$order = $this->query['order'];
			if(!is_array($field)) $field = array($field => $type);
			foreach($field as $by => $t){
				if(is_int($by) && !preg_match('/desc|asc/', strtolower($t))){
					$by = $t;
					$t = $type;
				}
				$order .= sprintf("`%s` %s, ", $by, $t);
			}
			$this->query['order'] = $order;
			return $this;
		}
		
		/**
		 * Our query builder helper method. Returns a PDO statement object
		 */
		
		private function run_query($query){
			// append query helpers to the query
			if(!empty($this->query['where'])){
				$query .= ' WHERE '.$this->query['where'];
			}
			if(!empty($this->query['order'])){
				$query .= ' ORDER BY '.substr($this->query['order'], 0, -2);
			}
			if(!empty($this->query['limit'])){
				$query .= ' LIMIT '.$this->query['limit'];
			}
			
			// clear query helper info
			$this->query = array();
			
			// create a PDO statement object
			$stmt = self::$connection->prepare($query);
			
			// run query
			$stmt->execute($this->params);
			
			// set the last query
			foreach($this->params as $param => $value){
				$query = str_replace(':'.$param, '\''.$value.'\'', $query);
			}
			self::last_query($query);
			
			// clear params
			$this->params = array();
			
			return $stmt;
		}
		
		/**
		 * Main SQL methods
		 */
		
		public function get($fields = '*'){
			if(is_array($fields)){
				foreach($fields as $f){
					$tmp .= "`{$f}`,";
				}
				$fields = substr($tmp, 0, -1);
			}
			$qry = sprintf("SELECT %s FROM %s", $fields, $this->table);
			$stmt = $this->run_query($qry);
			self::num_rows($stmt->rowCount());
			$stmt->setFetchMode(\PDO::FETCH_ASSOC);
			$data = array();
			while($row = $stmt->fetch()){
				// if limit 1, return the single row
				if(preg_match('/LIMIT 1( |\z)/', self::last_query())) return $row;
				array_push($data, $row);
			}
			return $data;
		}
		
		public function insert($data){
			if(!is_array($data)){
				$type = gettype($data);
				throw new \LogicException("MySQL::insert() expects an array, {$type} given");
			}elseif(is_multi($data)){
				// data[0] is the field list
				$fields = array_shift($data);
				foreach($fields as $f){
					$field .= "`{$f}`, ";
				}
				$qry = sprintf("INSERT INTO %s (%s) VALUES ", $this->table, substr($field, 0, -2));
				$values = '';
				foreach($data as $insert){
					$tmp_values = '';
					foreach($insert as $index => $value){
						$param = $this->__params($fields[$index], $value);
						$tmp_values .= ":{$param}, ";
					}
					$values .= '('.substr($tmp_values, 0, -2).'),';
				}
				$qry .= substr($values, 0, -1);
			}else{
				foreach($data as $field => $value){
					$param = $this->__params($field, $value);
					$fields .= "`{$field}`, ";
					$values .= ":{$param}, ";
				}
				$qry = sprintf("INSERT INTO %s (%s) VALUES (%s)", $this->table, substr($fields, 0, -2), substr($values, 0, -2));
			}
			$stmt = $this->run_query($qry);
			self::insert_id(self::$connection->lastInsertId());
			self::num_rows($stmt->rowCount());
			return true;
		}
		
		public function update($data, $where = null){
			if(is_array($where)) $this->where($where);
			if(!is_array($data)){
				$type = gettype($data);
				throw new \LogicException("MySQL::update expects an array, {$type} given");
			}elseif(empty($this->query['where']) && $where !== true){
				throw new \Exception('WHERE is not set in your query, this will update all rows. Skipping query');
			}else{
				foreach($data as $field => $value){
					$param = $this->__params($field, $value);
					$update .= sprintf("`%s` = :%s, ", $field, $param);
				}
				$qry = sprintf("UPDATE %s SET %s", $this->table, substr($update, 0, -2));
				$stmt = $this->run_query($qry);
				self::num_rows($stmt->rowCount());
				return true;
			}
		}
		
		public function set($key, $value, $where = null){
			if(is_array($where)) self::where($where);
			if(is_array($key) || is_array($value)){
				throw new \LogicException("MySQL::set is used for setting a single column. If you wish to set multiple columns, use MySQL::update");
			}elseif(empty($this->query['where']) && $where !== true){
				throw new \Exception('WHERE is not set in your query, this will update all rows. Skipping query');
			}else{
				return $this->update(array($key => $value), $where);
			}
		}
		
		public function delete($where = null){
			if(is_array($where)) $this->where($where);
			if(empty($this->query['where']) && $where !== true){
				throw new \Exception('WHERE is not set in your query, this will delete all rows. Skipping query');
			}else{
				$qry = sprintf("DELETE FROM %s", $this->table);
				$stmt = $this->run_query($qry);
				self::num_rows($stmt->rowCount());
				return true;
			}
		}
	
	}