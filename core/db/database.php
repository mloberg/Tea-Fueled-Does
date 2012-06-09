<?php namespace TFD\Core\DB;

	interface Database{
	
		/**
		 * Returns the last query ran
		 */
		
		public static function last_query();
		
		/**
		 * Return the number of rows returned
		 */
		
		public static function num_rows();
		
		/**
		 * Get the last inserted id
		 */
		
		public static function insert_id();
		
		/**
		 * Return a new database class
		 */
		
		public static function table($table);
		
		/**
		 * Run a raw query
		 */
		
		public static function query($query, $return);
		
		/**
		 * Limit result based on field value
		 */
		
		public function where($field, $equal);
		
		/**
		 * Limit result based on multiple field value
		 */
		
		public function and_where($field, $equal);
		
		/**
		 * Limit result based on multiple field value
		 */
		
		public function or_where($field, $equal);
		
		/**
		 * Limit the number of returned results
		 */
		
		public function limit($limit);
		
		/**
		 * Order result by a field
		 */
		
		public function order_by($field, $type);
		
		/**
		 * Get (a) record(s)
		 */
		
		public function get($fields);
		
		/**
		 * Insert a record
		 */
		
		public function insert($data);
		
		/**
		 * Update (a) record(s)
		 */
		
		public function update($data);
		
		/**
		 * Delete (a) record(s)
		 */
		
		public function delete();
	
	}