<?php namespace TFD\Core\Cache;

	interface Driver{
	
		/**
		 * Check for the existance of a key
		 */
		
		public function has($key);
		
		/**
		 * Get the value of a key
		 */
		
		public function get($key);
		
		/**
		 * Set a key
		 */
		
		public function set($key, $value, $time);
		
		/**
		 * Delete a key
		 */
		
		public function delete($key);
		
		/**
		 * Delete all keys
		 */
		
		public function flush();
	
	}