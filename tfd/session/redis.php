<?php namespace TFD\Session;

	use TFD\Config;
	use TFD\Redis as Store;

	class Redis implements Session {

		private $save_path;

		/**
		 * Open a session.
		 *
		 * @param string $save_path Set by session_save_path
		 * @param string $session_name Session name
		 * @return boolean True if session was opened
		 */

		public function open($save_path, $session_name) {
			$save_path = explode(':', $save_path);
			$host = $save_path[0] ?: Config::get('redis.host');
			$port = ($save_path[1] ?: Config::get('redis.port')) ?: 6379;
			$auth = $save_path[2] ?: Config::get('redis.auth');
			$this->save_path = new Store($host, $port, $auth);
			return true;
		}

		/**
		 * Close the session.
		 * 
		 * @return boolean True
		 */

		public function close() {
			return true;
		}

		/**
		 * Get the session data.
		 * 
		 * @param string $id Session name
		 * @return string Session data
		 */

		public function read($id) {
			return $this->save_path->hget('session:data', $id);
		}

		/**
		 * Write session data.
		 * 
		 * @param string $id Session name
		 * @param string $data Session data
		 * @return boolean True if session data was written
		 */

		public function write($id, $data) {
			$this->save_path->hset('session:life', $id, time());
			return (boolean)$this->save_path->hset('session:data', $id, $data);
		}

		/**
		 * Destroy session data.
		 * 
		 * @param string $id Session name
		 * @return boolean True if session data was deleted
		 */

		public function destroy($id) {
			$this->save_path->hdel('session:life', $id);
			return $this->save_path->hdel('session:data', $id);
		}

		/**
		 * Session garbage collection.
		 * 
		 * Cleanup session data.
		 * 
		 * @param integer $maxlifetime Lifetime of sessions
		 * @return boolean
		 */

		public function gc($maxlifetime) {
			foreach($this->save_path->hgetall('session:life', 'to_assoc') as $id => $lifetime) {
				if ($lifetime + $maxlifetime < time()) {
					$this->destroy($id);
				}
			}
			return true;
		}

	}
