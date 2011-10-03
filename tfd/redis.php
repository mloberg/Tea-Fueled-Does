<?php namespace TFD;

	/**
	 * iRedis
	 *
	 * @package		iRedis
	 * @version		1.0
	 * @author		Dan Horrigan <http://dhorrigan.com>
	 * @license		MIT License
	 * @copyright	2010 Dan Horrigan
	 *
	 * Permission is hereby granted, free of charge, to any person obtaining a copy
	 * of this software and associated documentation files (the "Software"), to deal
	 * in the Software without restriction, including without limitation the rights
	 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
	 * copies of the Software, and to permit persons to whom the Software is
	 * furnished to do so, subject to the following conditions:
	 *
	 * The above copyright notice and this permission notice shall be included in
	 * all copies or substantial portions of the Software.
	 *
	 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
	 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
	 * THE SOFTWARE.
	 */
	
	/**
	 * This code is closely related to Redisent, a Redis interface for the modest.
	 * Some code is from Redisent and has the following copyrights:
	 *
	 * @author Justin Poliey <jdp34@njit.edu>
	 * @copyright 2009 Justin Poliey <jdp34@njit.edu>
	 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
	 */
	
	if(!defined('CRLF')){
		define('CRLF', sprintf('%s%s', chr(13), chr(10)));
	}
	
	use TFD\Config;
	
	class Redis{
	
		const ERROR = '-';
		const INLINE = '+';
		const BULK = '$';
		const MULTIBULK = '*';
		const INTEGER = ':';
	
		protected $connection = false;
	
		/**
		 * Open the connection to the Redis server.
		 */
		public function  __construct($config = array()){
			$default = array('hostname' => Config::get('redis.host'), 'port' => Config::get('redis.port'));
			$config = $config + $default;
			$this->connection = @fsockopen($config['hostname'], $config['port'], $errno, $errstr);
	
			if(!$this->connection){
				throw new RedisException($errstr, $errno);
			}elseif(REDIS_PASS !== ''){
				// if Redis has a password, send the auth command
				$this->auth(REDIS_PASS);
			}
		}
	
		/**
		 * Closes the connection to the Redis server.
		 */
		public function  __destruct(){
			fclose($this->connection);
		}
		
		public function __call($name, $args){
			$cmd = $this->buildCommand($name, $args);
			$this->sendCommand($cmd);
			
			return $this->readReply();
		}
		
		public function buildCommand($cmd, $args){
			// Start building the command
			$command = '*'.(count($args) + 1).CRLF;
			$command .= '$'.strlen($cmd).CRLF;
			$command .= strtoupper($cmd).CRLF;
			
			// Add all the arguments to the command
			foreach($args as $arg){
				$command .= '$'.strlen($arg).CRLF;
				$command .= $arg.CRLF;
			}
			
			return $command;
		}
		
		public function sendCommand($command){
			if(!$this->connection){
				throw new RedisException('You must be connected to a Redis server to send a command.');
			}
	
			fwrite($this->connection, $command);
		}
		
		public function readReply(){
			if(!$this->connection){
				throw new RedisException('You must be connected to a Redis server to send a command.');
			}
			
			$reply = trim(fgets($this->connection, 512));
			
			switch(substr($reply, 0, 1)){
				case redis::ERROR:
					throw new RedisException(substr(trim($reply), 4));
				break;
				case redis::INLINE:
					$response = substr(trim($reply), 1);
				break;
				case redis::BULK:
					if($reply == '$-1'){
						return null;
					}
					$response = $this->readBulkReply($reply);
				break;
				case redis::MULTIBULK:
					$count = substr($reply, 1);
					if($count == '-1'){
						return null;
					}
					
					$response = array();
					for($i = 0; $i < $count; $i++){
						$bulk_head = trim(fgets($this->connection, 512));
						$response[] = $this->readBulkReply($bulk_head);
					}
				break;
				case redis::INTEGER:
					$response = substr(trim($reply), 1);
				break;
				default:
					throw new RedisException("invalid server response:{$reply}");
				break;
			}
			
			return $response;
		}
		
		protected function readBulkReply($reply){
			if(!$this->connection){
				throw new RedisException('You must be connected to a Redis server to send a command.');
			}
			$response = null;
			
			$read = 0;
			$size = substr($reply, 1);
			
			while ($read < $size){
				// If the amount left to read is less than 1024 then just read the rest, else read 1024
				$block_size = ($size - $read) > 1024 ? 1024 : ($size - $read);
				$response .= fread($this->connection, $block_size);
				$read += $block_size;
			}
			// Get rid of the CRLF at the end
			fread($this->connection, 2);
			
			return $response;
		}
	
	}
	
	class RedisException extends \Exception{ }