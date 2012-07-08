<?php namespace TFD\Core\Cache;

	use TFD\Core\Memcached as Store;
	
	class Memcached implements Driver {

		/**
		 * Check for the existance of a key.
		 *
		 * @param string $key Cache key
		 * @return boolean True if key exists
		 */
		
		public function has($key) {
			return !is_null($this->get($key));
		}
		
		/**
		 * Get the value of a key.
		 *
		 * @param string $key Cache key
		 * @return mixed Cache value if exists, otherwise null
		 */
		
		public function get($key) {
			return (($item = Store::get($key)) !== false) ? $item : null;
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
			return Store::set($key, $value, $time);
		}

		/**
		 * Store an array of cache items.
		 *
		 * @param array $values A key value array of cache items
		 * @param integer $time Time to live in seconds
		 */

		public function store($values, $time = 0) {
			return Store::store($values, $time);
		}

		/**
		 * Get the item or store the value.
		 *
		 * @param string $key Cache key
		 * @param function $value A callback for the value
		 * @param integer $time Time to live
		 * @return mixed Cache value
		 */

		public function remember($key, $value, $time = 0) {
			if (!$this->has($key)) {
				$this->set($key, $value(), $time);
			}
			return $this->get($key);
		}
		
		/**
		 * Delete a cache item.
		 *
		 * @param string $key Cache key
		 * @return boolean True on success
		 */
		
		public function delete($key) {
			return Store::delete($key);
		}
		
		/**
		 * Delete all cache items.
		 *
		 * @return boolean True on success
		 */
		
		public function flush() {
			return Store::flush();
		}
	
	}
