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
			$errno = $errstr = null;
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
			$command = $this->build($method, $args);
			fwrite($this->connection, $command);
			return $this->read_reply();
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
				case ':': // integer
					if ($reply == 'OK') return true;
					return $reply;
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

	}
