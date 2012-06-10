<?php namespace TFD\Core;

	class File {
	
		/**
		 * Get the contents of a file.
		 * 
		 * @param string $path File
		 * @return string File contents
		 */

		public static function get($path) {
			return file_get_contents($path);
		}

		/**
		 * Put content into a file.
		 * 
		 * @param string $path File
		 * @param string $data Content
		 * @return boolean|integer Number of bytes written to file or false if not written
		 */
		
		public static function put($path, $data) {
			return file_put_contents($path, $data, LOCK_EX);
		}

		/**
		 * Append content to a file.
		 * 
		 * @param string $path File
		 * @param string $data Content to append
		 */
		
		public static function append($path, $data) {
			return file_put_contents($path, $data, LOCK_EX | FILE_APPEND);
		}

		/**
		 * Get a line, and surrounding, from a file.
		 * 
		 * @param string $path File
		 * @param integer $line Line to get
		 * @param integer $padding Lines around to get
		 * @return array Fiel lines
		 */
		
		public static function snapshot($path, $line, $padding = 5) {
			if (!file_exists($path)) return array();
			$file = file($path, FILE_IGNORE_NEW_LINES);
			array_unshift($file, '');
			if (($start = $line - $padding) < 0) $start = 0;
			if (($length = ($line - $start) + $padding + 1) < 0) $length = 0;
			return array_slice($file, $start, $length, true);
		}
	
	}
