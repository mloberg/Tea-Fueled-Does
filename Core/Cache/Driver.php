<?php namespace TFD\Core\Cache;

	interface Driver {
	
		/**
		 * Check for the existance of a key.
		 *
		 * @param string $key Cache key
		 * @return boolean True if key exists
		 */
		
		public function has($key);
		
		/**
		 * Get the value of a key.
		 *
		 * @param string $key Cache key
		 * @return mixed Cache value if exists, otherwise null
		 */
		
		public function get($key);
		
		/**
		 * Set a single cache item.
		 *
		 * @param string $key Cache key
		 * @param mixed $value Cached value
		 * @param integer $time Time to live in seconds
		 * @return boolean True on success
		 */
		
		public function set($key, $value, $time = 0);

		/**
		 * Store an array of cache items.
		 *
		 * @param array $values A key value array of cache items
		 * @param integer $time Time to live in seconds
		 */

		public function store($values, $time = 0);
		
		/**
		 * Delete a cache item.
		 *
		 * @param string $key Cache key
		 * @return True on success
		 */
		
		public function delete($key);
		
		/**
		 * Delete all cache items.
		 */
		
		public function flush();
	
	}
