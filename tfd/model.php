<?php namespace TFD;

	/**
	 * Return an instance of a user model
	 */
	
	class Model{
	
		public static function __callStatic($name, $arguments){
			array_unshift($arguments, $name);
			return call_user_func_array('self::make', $arguments);
		}
		
		public static function make(){
			$args = func_get_args();
			$name = array_shift($args);
			if(empty($name)){
				throw new \Exception('Missing name argument for model::make');
			}
			$model = new \ReflectionClass('\Content\Models\\'.$name);
			return $model->newInstanceArgs($args);
		}
	
	}