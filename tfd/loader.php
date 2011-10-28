<?php namespace TFD;

	class Loader{
	
		private static $alias = array();
		private static $app = false;
		private static $content = false;
		
		public static function load($name){
			if(self::$app !== false && preg_match('/^TFD\\\/', $name)){
				$name = preg_replace('/^TFD/', self::$app, $name);
			}elseif(self::$content !== false && preg_match('/^content\\\/', strtolower($name))){
				$name = preg_replace('/^content/', self::$content, strtolower($name));
			}
			$file = BASE_DIR.strtolower(str_replace('\\', '/', $name)).EXT;
			if(file_exists($file)){
				include_once($file);
			}elseif(array_key_exists($name, self::$alias)){
				$file = (!is_null(self::$alias[$name]['file'])) ? self::$alias[$name]['file'] : BASE_DIR.strtolower(str_replace('\\', '/', self::$alias[$name]['class'])).EXT;
				if(self::$app !== false && preg_match('/^'.str_replace('/', '\/', BASE_DIR).'tfd\//', $file)){
					$file = preg_replace('/^('.str_replace('/', '\/', BASE_DIR).')tfd\//', '${1}'.self::$app.'/', $file);
				}elseif(self::$content !== false && preg_match('/^'.str_replace('/', '\/', BASE_DIR).'content\//', $file)){
					$file = preg_replace('/^('.str_replace('/', '\/', BASE_DIR).')content\//', '${1}'.self::$content.'/', $file);
				}
				if(file_exists($file)){
					include_once($file);
					class_alias(self::$alias[$name]['class'], $name);
				}else{
					throw new Exception("Could not load class {$name}! No file found at {$file}.");
				}
			}elseif(preg_match('/^models/', strtolower($name))){
				self::load_model($name);
			}else{
				throw new Exception("Could not load class {$name}! No file found at {$file}.");
			}
		}
		
		public static function app_dir($dir){
			self::$app = $dir;
		}
		
		public static function content_dir($dir){
			self::$content = $dir;
		}
		
		private static function load_model($model){
			$file = CONTENT_DIR.strtolower(str_replace('\\', '/', $model)).EXT;
			if(file_exists($file)){
				include_once($file);
				class_alias('\Content\\'.$model, $model);
			}else{
				throw new Exception("Could not load model {$model}! No file found at {$file}.");
			}
		}
		
		public static function add_alias($name, $class, $file = null){
			self::$alias[$name] = array(
				'class' => $class,
				'file' => $file
			);
		}
		
		public static function create_aliases($aliases){
			if(!is_array($aliases)){
				$type = gettype($aliases);
				throw new LogicException("Loader::create_aliases expects an array, {$type} sent.");
			}else{
				foreach($aliases as $name => $class){
					self::add_alias($name, $class);
				}
			}
		}
	
	}