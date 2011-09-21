<?php namespace TFD\DB;

	class MySQL{
	
		static private $connection;
		static private $info = array();
		static private $query_info = array();
		
		function __destruct(){
			// do some cleanup
			echo "desctruct";
			self::$link = null;
		}
		
		static function errors($qry=''){
			$error = mysql_errno() . ': ' . mysql_error();
			if(mysql_errno() == 1064){
				$error .= "\n<br />Query: {$qry}";
			}
			// this seems to be causing some errors...
			// use the built in error class to report errors?
			if(TESTING_MODE){
				echo $error;
			}else{
				die($error);
			}
		}
		
		/**
		 * Accessors
		 */
		
		private static function set($name, $value){
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
			return self::$connection->mysql();
		}
		
		public static function table($table){
			return new Query($table, self::connection());
		}
		
		public function get($fields = '*'){
			$sql = sprintf("SELECT * FROM %s", self::$query_info['table']);
			$stmt = self::connection()->query($sql);
			$stmt->setFetchMode(PDO::FETCH_ASSOC);
			while($row = $stmt->fetch()){
				print_p($row);
			}
			return;
		}
		
		private static function _where($info,$type='AND'){
			$link =& self::connection();
			$where = self::$where;
			foreach($info as $row => $value){
				if($where === null){
					$where = sprintf("WHERE `%s`='%s'", mysql_real_escape_string($row), mysql_real_escape_string($value));
				}else{
					$where .= sprintf(" %s `%s`='%s'", $type, mysql_real_escape_string($row), mysql_real_escape_string($value));
				}
			}
			self::$where = $where;
			mysql_close($link);
		}
		
		function where($field,$equal=null){
			if(is_array($field)){
				self::_where($field);
			}else{
				self::_where(array($field => $equal));
			}
			return $this;
		}
		
		function and_where($field,$equal=null){
			return $this->where($field,$equal);
		}
		
		function or_where($field,$equal=null){
			if(is_array($field)){
				self::_where($field, 'OR');
			}else{
				self::_where(array($field => $equal), 'OR');
			}
			return $this;
		}
		
		function limit($limit){
			$this->limit = 'LIMIT '.$limit;
			return $this;
		}
		
		function order_by($by,$type='DESC'){
			$order = 'ORDER BY ';
			if(is_array($by)){
				// multi
				foreach($by as $b){
					$order .= '`'.$b.'`, ';
				}
				$order = preg_replace('/, $/','',$order);
			}else{
				$order .= '`'.$by.'`';
			}
			$this->order_by = $order.' '.$type;
			return $this;
		}
		
		protected function extra(){
			$extra = self::$where.' '.$this->order_by.' '.$this->limit;
			// cleanup
			self::$where = null;
			$this->limit = null;
			$this->order_by = null;
			return $extra;
		}
		
		function qry($qry,$return=false){
			$link =& self::connection();
			self::$last_query = $qry;
			$result = mysql_query($qry);
			if(is_resource($result)){
				self::$num_rows = mysql_num_rows($result);
			}
			if($return){
				// return
				$data = array();
				while($row = mysql_fetch_assoc($result)){
					$data[] = $row;
				}
				return $data;
			}
			return null;
		}
		
		function _get($table,$select='*'){
			$link =& self::connection();
			$data = array();
			$qry = sprintf('SELECT %s FROM %s %s', mysql_real_escape_string($select), mysql_escape_string($table), $this->extra());
			$result = mysql_query($qry) or self::errors($qry);
			if(is_resource($result)){
				self::$last_query = $qry;
				self::$num_rows = mysql_num_rows($result);
				if(self::$num_rows === 0){
					return false;
				}else{
					while($row = mysql_fetch_assoc($result)){
						$data[] = $row;
					}
					return $data;
				}
				mysql_free_result($result);
			}else{
				return false;
			}
		}
		
		function insert($table,$data){
			$link =& self::connection();
			$qry = sprintf('INSERT INTO %s', mysql_escape_string($table));
			foreach($data as $key => $val){
				$fields .= '`'.mysql_real_escape_string($key) . '`,';
				$values .= "'" . mysql_real_escape_string($val) . "',";
			}
			$fields = preg_replace('/,$/', '', $fields);
			$values = preg_replace('/,$/', '', $values);
			$qry = "{$qry} ({$fields}) VALUES ({$values})";
			self::$last_query = $qry;
			if(!mysql_query($qry)){
				die(mysql_error($link));
			}else{
				self::$insert_id = mysql_insert_id();
				return true;
			}
		}
		
		function update($table,$info,$where=''){
			$link =& self::connection();
			foreach($info as $key => $val){
				$update .= sprintf("`%s`='%s', ", mysql_real_escape_string($key), mysql_real_escape_string($val));
			}
			$update = preg_replace('/, $/', '', $update);
			if(is_array($where)){
				foreach($where as $key => $value){
					if(!$temp){
						$temp = sprintf("WHERE `%s`='%s'", mysql_real_escape_string($key), mysql_real_escape_string($value));
					}else{
						$temp .= sprintf(" AND `%s`='%s'", mysql_real_escape_string($key), mysql_real_escape_string($value));
					}
				}
				$where = $temp;
			}else{
				$where = $this->extra();
			}
			$qry = sprintf('UPDATE %s SET %s %s', mysql_real_escape_string($table), $update, $where);
			self::$last_query = $qry;
			if(!mysql_query($qry)){
				self::errors($qry);
			}else{
				return true;
			}
		}
		
		function delete($table,$where=''){
			$link =& self::connection();
			if(is_array($where)){
				foreach($where as $key => $value){
					if(!$temp){
						$temp = sprintf("WHERE `%s`='%s'", mysql_real_escape_string($key), mysql_real_escape_string($value));
					}else{
						$temp .= sprintf(" AND `%s`='%s'", mysql_real_escape_string($key), mysql_real_escape_string($value));
					}
				}
				$where = $temp;
			}else{
				$where = $this->extra();
			}
			$qry = sprintf('DELETE FROM %s %s', mysql_real_escape_string($table), $where);
			self::$last_query = $qry;
			if(!mysql_query($qry)){
				self::errors($qry);
			}else{
				return true;
			}
		}
	
	}