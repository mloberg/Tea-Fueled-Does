<?php namespace TFD\Session;

	/**
	 * 'session.handler' => 'file'
	 * 'session.save_path' => BASE_DIR.'storage/sessions/'
	 */

	class File implements Session {

		private $save_path;

		/**
		 * Open a session.
		 *
		 * @param string $save_path Set by session_save_path
		 * @param string $session_name Session name
		 * @return boolean True if session was opened
		 */

		public function open($save_path, $session_name) {
			$this->save_path = $save_path;
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
			return (string)@file_get_contents("{$this->save_path}sess_{$id}");
		}

		/**
		 * Write session data.
		 * 
		 * @param string $id Session name
		 * @param string $data Session data
		 * @return boolean True if session data was written
		 */

		public function write($id, $data) {
			return file_put_contents("{$this->save_path}sess_{$id}", $data) === false ?: true;
		}

		/**
		 * Destroy session data.
		 * 
		 * @param string $id Session name
		 * @return boolean True if session data was deleted
		 */

		public function destroy($id) {
			@unlink("{$this->save_path}sess_{$id}");
			return true;
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
			foreach (glob("{$this->save_path}sess_*") as $file) {
				if (filemtime($file) + $maxlifetime < time()) {
					@unlink($file);
				}
			}
			return true;
		}

	}
