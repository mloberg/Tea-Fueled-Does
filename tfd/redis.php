<?php namespace TFD;

	class RedisException extends \Exception { }

	class Redis {

		const CRLF = "\r\n";

		private static $instance;

		private $connection;

		/**
		 * Create a new instance of the class.
		 * 
		 * @param string $server Redis server
		 * @param integer $port Redis port
		 * @param string $auth Redis auth
		 */

		public function __construct($server, $port = 6379, $auth = null) {
			$this->connection($server, $port);
			if ($auth) {
				$this->auth($auth);
			}
		}

		/**
		 * Cleanup Redis connection.
		 */

		public function __destruct() {
			fclose($this->connection);
		}

		/**
		 * Foward all static calls to an instance of this class.
		 * 
		 * @param string $method Method call
		 * @param string $args Method arguments
		 * @return mixed Redis response
		 */

		public static function __callStatic($method, $args) {
			if (!static::$instance) {
				static::$instance = new static(Config::get('redis.host'), Config::get('redis.port'), Config::get('redis.auth'));
			}
			return call_user_func_array(array(static::$instance, $method), $args);
		}

		/**
		 * Open a new connection to the Redis server.
		 *
		 * @param string $server Redis server
		 * @param integer $port Redis port
		 */

		private function connection($server, $port = 6379) {
			if (!$this->connection) {
				if (!$this->connection = fsockopen($server, $port, $errno, $errstr)) {
					throw new \RedisException($errstr, $errno);
				}
			}
			return $this->connection;
		}

		/**
		 * Catch all calls that don't exist and interpret them as Redis calls.
		 *
		 * @param string $method Method call
		 * @param string $args Method arguments
		 * @return mixed Redis response
		 */

		public function __call($method, $args) {
			$check_for_callback = end($args);
			if (is_callable($check_for_callback) || method_exists(__CLASS__, $check_for_callback)) {
				$callback = array_pop($args);
			}
			$command = $this->build($method, $args);
			fwrite($this->connection, $command);
			$reply = $this->read_reply();
			if (is_string($callback)) {
				return call_user_func(array($this, $callback), $reply);
			} elseif ($callback) {
				return $callback($reply);
			}
			return $reply;
		}

		/**
		 * Build a valid Redis call.
		 *
		 * @param string $method Redis command name
		 * @param array $args Redis command arguments
		 * @return string Redis command
		 */

		private function build($method, $args) {
			$command = array();
			$command[] = '*' . (count($args) + 1);

			$command[] = '$' . strlen($method);
			$command[] = strtoupper($method);

			foreach ($args as $arg) {
				$command[] = '$' . strlen($arg);
				$command[] = $arg;
			}

			return implode(self::CRLF, $command) . self::CRLF;
		}

		/**
		 * Return the Redis server response.
		 * 
		 * @return mixed Redis server response
		 */

		private function read_reply() {
			$reply = fgets($this->connection);
			$status = substr($reply, 0, 1);
			$reply = trim(substr($reply, 1));
			switch ($status) {
				case '-': // error
					throw new RedisException($reply);
					break;
				case '+': // single line
					if ($reply == 'OK') return true;
					return $reply;
					break;
				case ':': // integer
					return (integer)$reply;
					break;
				case '$': // bulk
					return $this->bulk_reply($reply);
					break;
				case '*':
					$resp = array();
					for ($i=0; $i < $reply; $i++) { 
						$resp[$i] = $this->read_reply();
					}
					return $resp;
					break;
				default:
					throw new RedisException('Unexpected response');
					break;
			}
		}

		/**
		 * Read a bulk reply ($)
		 *
		 * @param integer $size Size of the reply
		 * @return mixed Redis response
		 */

		private function bulk_reply($size) {
			if ($size === '-1') return null;
			$data = '';
			$read = 0;
			while ($read < $size) {
				if (($chunk = ($size - $read)) > 8192) {
					$chunk = 8192;
				}
				$data .= fread($this->connection, $chunk);
				$read += $chunk;
			}
			fread($this->connection, 2);
			return $data;
		}

		/**
		 * Convert a Redis reply into an associative array.
		 * 
		 * @param array $reply Array to turn into associative array
		 * @return array Associative array
		 */

		public static function to_assoc($reply) {
			$keys = array();
			$values = array();
			foreach ($reply as $key => $value) {
				if ($key & 1) {
					$values[] = $value;
				} else {
					$keys[] = $value;
				}
			}
			return array_combine($keys, $values);
		}

	}
