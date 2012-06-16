<?php namespace TFD\Core\Cache;

	use TFD\Core\Config;
	
	class APC implements Driver {
	
		/**
		 * Check for the existance of a key.
		 *
		 * @param string $key Cache key
		 * @return boolean True if key exists
		 */
		
		public function has($key) {
			return apc_exists($key);
		}
		
		/**
		 * Get the value of a key.
		 *
		 * @param string $key Cache key
		 * @return mixed Cache value if exists, otherwise null
		 */
		
		public function get($key) {
			return (($cache = apc_fetch($key)) !== false) ? $cache : null;
		}
		
		/**
		 * Set a single cache item.
		 *
		 * @param string $key Cache key
		 * @param mixed $value Cached value
		 * @param integer $time Time to live in seconds
		 * @return boolean True on success
		 */
		
		public function set($key, $value, $time = 0) {
			return apc_store($key, $value, $time);
		}

		/**
		 * Store an array of cache items.
		 *
		 * @param array $values A key value array of cache items
		 * @param integer $time Time to live in seconds
		 */

		public function store($values, $time = 0) {
			return apc_store($values, null, $time);
		}
		
		/**
		 * Delete a cache item.
		 *
		 * @param string $key Cache key
		 * @return True on success
		 */
		
		public function delete($key) {
			return apc_delete($key);
		}
		
		/**
		 * Delete all cache items.
		 */
		
		public function flush() {
			return apc_clear_cache('user'); // clear only user cache
		}

		/**
		 * Increase a stored number.
		 *
		 * @param string $key Cache key
		 * @param integer $count Increase by
		 * @return integer Key's value
		 */
		
		public function increase($key, $count = 1) {
			return apc_inc($key, $count);
		}

		/**
		 * Decrease a stored number.
		 *
		 * @param string $key Cache key
		 * @param integer $count Decrease by
		 * @return integer Key's value
		 */
		
		public function decrease($key, $count = 1) {
			return apc_dec($key, $count);
		}
	
	}
