<?php namespace TFD;

	class Loader{
	
		private static $alias = array();
		
		public static function load($name){
			$file = BASE_DIR.strtolower(str_replace('\\', '/', $name)).EXT;
			if(file_exists($file)){
				include_once($file);
			}elseif(array_key_exists($name, self::$alias)){
				$file = (!is_null(self::$alias[$name]['file'])) ? self::$alias[$name]['file'] : BASE_DIR.strtolower(str_replace('\\', '/', self::$alias[$name]['class'])).EXT;
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