<?php namespace TFD\DB;

	class Query extends MySQL{
	
		private static $connection;
		private $table;
		private static $info = array();
		private static $placeholders = array();
		
		function __construct($table, $connection){
			$this->table = $table;
			self::$connection = $connection;
		}
		
		function __destruct(){
			self::$info = array();
			self::$placeholders = array();
			self::$connection = null;
		}
		
		/**
		 * Helpers
		 */
		
		private function __where($field, $value, $type = 'AND'){
			$where = self::$info['where'];
			if(!is_array($field)) $field = array($field => $value);
			foreach($field as $col => $value){
				if(empty($where)){
					$where = sprintf("`%s` = :%s", $col, $col);
				}else{
					$where .= sprintf(" %s `%s` = :%s", $type, $col, $col);
				}
				self::$placeholders[$col] = $value;
			}
			self::$info['where'] = $where;
		}
		
		public function where($field, $equal = null){
			self::__where($field, $equal);
			return $this;
		}
		
		public function and_where($field, $equal = null){
			return $this->where($field, $equal);
		}
		
		public function or_where($field, $equal = null){
			self::__where($field, $equal, 'OR');
			return $this;
		}
		
		private function __like($fields, $type = 'AND'){
			$where = self::$info['where'];
			if(!is_array($fields)) $fields = array($fields => $type);
			foreach($fields as $col => $value){
				if(empty($where)){
					$where = sprintf("`%s` LIKE '%s'", $col, $value.'%');
				}else{
					$where .= sprintf(" %s `%s` LIKE '%s'", $type, $col, $value.'%');
				}
			}
			self::$info['where'] = $where;
		}
		
		public function like($field, $like = null){
			self::__like($field, $like);
			return $this;
		}
		
		public function and_like($field, $like = null){
			return $this->like($field, $like);
		}
		
		public function or_like($field, $like = null){
			self::__like($field, $like, 'OR');
			return $this;
		}
		
		public function limit($limit){
			self::$info['limit'] = $limit;
			return $this;
		}
		
		public function order_by($order_by, $order_type = 'DESC'){
			$order = self::$info['order'];
			if(!is_array($order_by)) $order_by = array($order_by => $order_type);
			foreach($order_by as $col => $type){
				if(is_int($col) && !preg_match('/desc|asc/', strtolower($type))){
					$col = $type;
					$type = $order_type;
				}
				$order .= sprintf("`%s` %s, ", $col, $type);
			}
			self::$info['order'] = $order;
			return $this;
		}
		
		/**
		 * Our query builder method.
		 * Take our query, adds the extra info, creates the PDO statement, and binds the parameters to it.
		 */
		
		private function query_builder($query){
			if(!empty(self::$info['where'])){
				$query .= ' WHERE '.self::$info['where'];
			}
			if(!empty(self::$info['order'])){
				$query .= ' ORDER BY '.substr(self::$info['order'], 0, -2);
			}
			if(!empty(self::$info['limit'])){
				$query .= ' LIMIT '.self::$info['limit'];
			}
			
			$stmt = self::$connection->prepare($query);
			
			foreach(self::$placeholders as $param => $value){
				$query = str_replace(':'.$param, '\''.$value.'\'', $query);
			}
						
			// set last_query
			parent::set('last_query', $query);
			
			return $stmt;
		}
		
		/**
		 * Main Methods
		 */
		
		public function get($fields = '*'){
			if(is_array($fields)){
				foreach($fields as $f){
					$tmp .= "`{$f}`,";
				}
				$fields = substr($tmp, 0, -1);
			}
			$qry = sprintf("SELECT %s FROM %s", $fields, $this->table);
			$stmt = self::query_builder($qry);
			try{
				$stmt->execute(self::$placeholders);
			}catch(\PDOException $e){
				throw new \TFD\Exception($e);
			}
			parent::set('num_rows', $stmt->rowCount());
			$stmt->setFetchMode(\PDO::FETCH_ASSOC);
			$data = array();
			while($row = $stmt->fetch()){
				// if limit 1, return the single row
				if(preg_match('/LIMIT 1/', parent::last_query())){
					return $row;
				}
				$data[] = $row;
			}
			return $data;
		}
		
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