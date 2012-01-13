<?php namespace TFD\Test;

	class Fixture{
		
		private $data = array();

		public function __construct($load){
			$fixture = CONTENT_DIR.'tests/fixtures/'.$load.EXT;
			echo $fixture;
			if(!file_exists($fixture)){
				throw new \Exception('Fixture does not exist');
			}
			$this->data = include($fixture);
		}

		public function __get($name){
			if(!array_key_exists($name, $this->data)){
				throw new \Exception("Could not find {$name} in fixture");
			}
			if(is_callable($this->data[$name])){
				return $this->data[$name]();
			}
			return $this->data[$name];
		}

		public static function load($fixture){
			return new self($fixture);
		}

	}