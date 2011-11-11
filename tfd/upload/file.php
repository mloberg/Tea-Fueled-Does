<?php namespace TFD\Upload;

	class File{
		
		private static $info = array();
		
		function __construct($file){
			if(!isset($_FILES[$file])){
				throw new \Exception("\$_FILES[{$file}] not set");
			}else{
				self::$info = $_FILES[$file];
			}
		}
		
		/**
		 * Getters
		 */
		
		public function name(){
			return self::$info['name'];
		}
		
		public function mime(){
			return self::$info['type'];
		}
		
		public function size($type = ''){
			$bytes = self::$info['size'];
			switch($type){
				case 'kb':
					return round($bytes / 1024);
				case 'mb':
					return round(($bytes / 1048576), 2);
				case 'gb':
					return round(($bytes / 1073741824), 1);
				default:
					return $bytes;
			}
		}
		
		/**
		 * Class methods
		 */
		
		public function save($path = null, $name = null, $force = false){
			if(is_null($path) || empty($path)) $path = PUBLIC_DIR;
			if(is_null($name) || empty($name)) $name = basename($this->name());
			if(!preg_match('/\/$/', $path)) $path .= '/';
			$target = $path.$name;
			if(file_exists($target) && $force !== true){
				throw new \Exception("{$target} exists. If you wish to overwrite this file, pass true as the third parameter");
				return false;
			}else{
				return (move_uploaded_file(self::$info['tmp_name'], $target));
			}
		}
		
		public function extension(){
			return end(explode('.', self::$info['name']));
		}
		
		public function type(){
			switch($this->mime()){
				case 'image/png':
				case 'image/jpeg':
				case 'image/gif':
					return 'image';
				case 'video/quicktime':
				case 'video/x-flv':
					return 'video';
				case 'application/pdf':
					return 'pdf';
				case 'text/csv':
					return 'csv';
				case 'application/vnd.ms-excel':
					return 'excel';
				case 'apllication/x-zip':
					return 'zip';
				case 'text/plain':
					return 'text';
				default:
					return $this->mime();
			}
		}
		
		public function is_type($type){
			$filetype = $this->type();
			return ($type == $filetype);
		}
		
		public function is_image(){
			return ($this->type() == 'image');
		}
	
	}