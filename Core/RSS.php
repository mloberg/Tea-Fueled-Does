<?php namespace TFD\Core;

	use TFD\Core\Response;
	
	class RSS{
	
		private $info, $items = array();
		
		public function __construct($info = array()){
			$this->info = $info;
		}
		
		public static function make($info = array()){
			return new self($info);
		}
		
		public static function item($info = array()){
			return new Item($info);
		}
		
		public function add($info){
			if(gettype($info) == 'object'){
				$info = unserialize((string)$info);
			}elseif(!isset($item['title'], $item['description'], $item['link'])){
				throw new \Exception("RSS item is missing mandatory info (title, link, and description)");
			}
			if(!isset($info['pubDate'])){
				$info['pubDate'] = date("D, d M Y H:i:s T");
			}else{
				$info['pubDate'] = date("D, d M Y H:i:s T", strtotime($info['pubDate']));
			}
			array_push($this->items, $info);
			return true;
		}
		
		public function __set($name, $value){
			$this->info[$name] = $value;
		}
		
		public function __get($name){
			if(array_key_exists($this->info, $name)){
				return $this->info[$name];
			}
			return null;
		}
		
		/**
		 * Render out RSS feed
		 */
		
		private function __rssfeed(){
			// validate info
			if(!isset($this->info['title'], $this->info['link'], $this->info['description'])){
				throw new \Exception("RSS is missing mandatory info (title, link, and description)");
			}elseif(empty($this->items)){
				throw new \Exception("RSS items are empty");
			}elseif(!isset($this->info['pubDate'])){
				$this->info['pubDate'] = date("D, d M Y H:i:s T");
			}
			$rss = '<?xml version="1.0" encoding="ISO-8859-1"?>'
				 . '<rss version="2.0">'
				 . '<channel>'
				 . $this->__tags($this->info);
			foreach($this->items as $item){
				$rss .= '<item>' . $this->__tags($item) . '</item>';
			}
			$rss .= '</channel>';
			$rss .= '</rss>';
			return $rss;
		}
		
		private function __tags($tags){
			$render = '';
			foreach($tags as $key => $value){
				$info .= "<{$key}>{$value}</{$key}>";
			}
			return $info;
		}
		
		/**
		 * Return RSS, no headers
		 */
		
		public function __toString(){
			return $this->__rssfeed();
		}
		
		/**
		 * Return the RSS and headers (via Core\Response)
		 */
		
		public function render(){
			$feed = $this->__rssfeed();
			$rss = Response::make($feed);
			$rss->header('Content-Type', 'application/xml; charset=ISO-8859-1');
			return $rss->send();
		}
		
		public static function header(){
			header('Content-Type: application/xml; charset=ISO-8859-1');
		}
	
	}
	
	class Item{
	
		private $info = array();
		
		public function __construct($info = array()){
			$this->info = $info;
		}
		
		public function __set($name, $value){
			$this->info[$name] = $value;
		}
		
		public function __get($name){
			if(array_key_exists($this->info, $name)){
				return $this->info[$name];
			}
			return null;
		}
		
		public function __toString(){
			return serialize($this->info);
		}
	
	}