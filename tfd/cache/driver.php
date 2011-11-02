<?php namespace TFD\Cache;

	interface Driver{
	
		/**
		 * Check for the existance of a key
		 */
		
		public static function has($key);
		
		/**
		 * Get the value of a key
		 */
		
		public static function get($key);
		
		/**
		 * Set a key
		 */
		
		public static function set($key, $value, $time);
		
		/**
		 * Delete a key
		 */
		
		public static function delete($key);
	
	}