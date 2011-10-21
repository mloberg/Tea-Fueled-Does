<?php namespace TFD\Tea;

	/**
	 * Get TFD ready to go
	 */
	
	use TFD\Tea\Database;
	
	class Init{
	
		public static function action($args){
			// database init
			Database::action('-i');
		}
	
	}