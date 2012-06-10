<?php namespace TFD\Core;

	/**
	 * Return an instance of a user model
	 */
	
	class Model {
	
		/**
		 * Allow for static calls to return a new instance of a model.
		 *
		 * Example: Model::model_name();
		 */

		public static function __callStatic($name, $arguments) {
			array_unshift($arguments, $name);
			return call_user_func_array('self::make', $arguments);
		}

		/**
		 * Return a new instance of a model.
		 */
		
		public static function make() {
			$args = func_get_args();
			$name = array_shift($args);
			if (empty($name)){
				throw new \Exception('Missing name argument for model::make');
			}
			$model = new \ReflectionClass('\Content\Models\\'.$name);
			return $model->newInstanceArgs($args);
		}
	
	}
