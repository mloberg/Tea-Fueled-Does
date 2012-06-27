<?php namespace TFD\Core\Cache;

	/**
	 * 'cache.driver' => 'file',
	 * 'cache.save_path' => BASE_DIR.'storage/cache/',
	 */

	use TFD\Core\Config;
	
	class File implements Driver {

		/**
		 * Check for the existance of a key.
		 *
		 * @param string $key Cache key
		 * @return boolean True if key exists
		 */
		
		public function has($key) {
			return !is_null(static::get($key));
		}
		
		/**
		 * Get the value of a key.
		 *
		 * @param string $key Cache key
		 * @return mixed Cache value if exists, otherwise null
		 */
		
		public function get($key) {
			$file = Config::get('cache.save_path').$key;
			if (!file_exists($file)) {
				return null;
			}
			list($expire, $content) = explode("\n", file_get_contents($file));
			if ($expire >= time()) {
				return unserialize($content);
			} else {
				@unlink($file);
				return null;
			}
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
			$expire = time() + $time;
			$content = $expire . "\n" . serialize($value);
			return (bool)file_put_contents(Config::get('cache.save_path') . $key, $content);
		}

		/**
		 * Store an array of cache items.
		 *
		 * @param array $values A key value array of cache items
		 * @param integer $time Time to live in seconds
		 */

		public function store($values, $time = 0) {
			foreach ($values as $key => $value) {
				static::set($key, $value, $time);
			}
		}
		
		/**
		 * Delete a cache item.
		 *
		 * @param string $key Cache key
		 * @return boolean True on success
		 */
		
		public function delete($key) {
			$file = Config::get('cache.save_path').$key;
			@unlink($file);
			return !file_exists($file);
		}
		
		/**
		 * Delete all cache items.
		 *
		 * @return boolean True on success
		 */
		
		public function flush() {
			foreach (glob(Config::get('cache.save_path').'*') as $file) {
				@unlink($file);
			}
			$files = glob(Config::get('cache.save_path').'*');
			return empty($files);
		}
	
	}
