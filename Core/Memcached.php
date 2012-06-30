<?php namespace TFD\Core;

	use TFD\Core\Config;
	
	class MemcachedException extends \Exception { }

	class Memcached {
	
		private static $instance = null;
		private static $ext = 'memcached';

		/**
		 * Start a connection to Memcached.
		 *
		 * @return boolean True on success
		 */
		
		public static function connect() {
			if (!is_null(static::$instance)) return true;
			static::$ext = Config::get('memcached.class') ?: 'memcached';
			if (!class_exists(static::$ext, false)) {
				throw new MemcachedException("{$ext} is not available on this system");
			} elseif (static::$ext === 'memcached') {
				static::$instance = new \Memcached;
				$servers = array();
				foreach (Config::get('memcached.servers') as $server) {
					$servers[] = array($server['host'], $server['port'] ?: 11211, $server['weight'] ?: 0);
				}
				static::$instance->addServers($servers);
			} elseif (static::$ext === 'memcache') {
				static::$instance = new \Memcache;
				foreach (Config::get('memcached.servers') as $server) {
					static::add_server($server['host'], $server['port'] ?: 11211, $server['weight'] ?: 0);
				}
			} else {
				throw new MemcachedException("Unknown class {$ext}");
			}

			if (static::$instance->getVersion() === false) {
				throw new MemcachedException("Could not establish any connections");
			}
			return true;
		}

		/**
		 * Add a server to the Memcached pool.
		 *
		 * @param string $host Memcached host
		 * @param integer $port Memcached port
		 * @param integer $weight Server weight
		 * @return boolean True on successful connection
		 */

		public static function add_server($host, $port = 11211, $weight = 100) {
			if (is_null(static::$instance)) static::connect();
			if (static::$ext === 'memcached') {
				return static::$instance->addServer($host, $port, $weight);
			}
			return static::$instance->addServer($host, $port, true, $weight);
		}

		/**
		 * Set an item in Memcached.
		 *
		 * @param string $key Item key
		 * @param mixed $value Item value
		 * @param integer $expire Item lifetime
		 * @return boolean True on success
		 */

		public static function set($key, $value, $expire = 0) {
			if (is_null(static::$instance)) static::connect();
			if (static::$ext === 'memcached') {
				return static::$instance->set($key, $value, $expire);
			}
			return static::$instance->set($key, $value, 0, $expire);
		}

		/**
		 * Get an item in Memcached.
		 *
		 * @param string $key Item key
		 * @param function $callback A callback function
		 * @return mixed Memcached value (false on non-existent)
		 */

		public static function get($key, $callback = null) {
			if (is_null(static::$instance)) static::connect();
			$value = static::$instance->get($key);
			if (is_callable($callback)) {
				return $callback($key, $value);
			}
			return $value;
		}

		/**
		 * Replace a Memcached key.
		 *
		 * @param string $key Item key
		 * @param mixed $value Item value
		 * @param integer $expire Item lifetime
		 * @return boolean True on success
		 */

		public static function replace($key, $value, $expire = 0) {
			if (is_null(static::$instance)) static::connect();
			if (static::$ext === 'memcached') {
				return static::$instance->replace($key, $value, $expire);
			}
			return static::$instance->replace($key, $value, 0, $expire);
		}

		/**
		 * Store multiple Memcached keys.
		 *
		 * @param array $values Array of key => values
		 * @param integer $expire Key lifetime
		 * @return boolean True on success
		 */

		public static function store($values, $expire = 0) {
			if (is_null(static::$instance)) static::connect();
			if (static::$ext === 'memcached') {
				return static::$instance->setMulti($values, $expire);
			}
			$return = array();
			foreach ($values as $key => $value) {
				$return[] = static::$instance->set($key, $value, 0, $expire);
			}
			return !in_array(false, $return);
		}

		/**
		 * Get multiple Memcached keys.
		 *
		 * @param array $keys Array of keys to get
		 * @param function $callback A callback function
		 * @return array Array of key => value
		 */

		public static function fetch($keys, $callback = null) {
			if (is_null(static::$instance)) static::connect();
			$values = array();
			foreach ($keys as $key) {
				$values[$key] = static::get($key, $callback);
			}
			return $values;
		}

		/**
		 * Return the instance of the Memcached class.
		 *
		 * @return object Memcache(d) object
		 */

		public static function instance() {
			if (is_null(static::$instance)) static::connect();
			return static::$instance;
		}

		/**
		 * Close the Memcached connection.
		 */

		public static function close() {
			if (is_null(static::$instance)) return;
			if (static::$ext === 'memcache') {
				static::$instance->close();
			}
			static::$instance = null;
		}

		/**
		 * Forward all other requests to the instance.
		 */

		public static function __callStatic($method, $args) {
			if (is_null(static::$instance)) static::connect();
			return call_user_func_array(array(static::$instance, $method), $args);
		}
	
	}
