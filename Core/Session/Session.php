<?php namespace TFD\Core\Session;

	interface Session {
	
		/**
		 * Open a session.
		 *
		 * @param string $save_path Set by session_save_path
		 * @param string $session_name Session name
		 * @return boolean True if session was opened
		 */
		
		public function open($save_path, $session_name);
		
		/**
		 * Close the session.
		 * 
		 * @return boolean True
		 */
		
		public function close();
		
		/**
		 * Read the session data.
		 * 
		 * @param string $id Session name
		 * @return string Session data
		 */
		
		public function read($id);
		
		/**
		 * Write session data.
		 * 
		 * @param string $id Session name
		 * @param string $data Session data
		 * @return boolean True if session data was written
		 */
		
		public function write($id, $data);
		
		/**
		 * Destroy session data.
		 * 
		 * @param string $id Session name
		 * @return boolean True if session data was deleted
		 */
		
		public function destroy($id);
		
		/**
		 * Session garbage collection.
		 * 
		 * Cleanup session data.
		 * 
		 * @param integer $maxlifetime Lifetime of sessions
		 * @return boolean
		 */
		
		public function gc($maxlifetime);
	
	}
