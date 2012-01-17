<?php namespace TFD;

	class Paginator{
	
		private $page;
		private $per_page = 10;
		private $last_page;
		private $total;
		private $results;
		private $url = false;
		private $append = array();
		
		public function __construct($results, $page = 1, $options = array()){
			$this->results = $results;
			$this->page = (empty($page)) ? 1 : $page;
			$this->total = count($results);
			if(!empty($options)){
				if(isset($options['per_page'])) $this->per_page = $options['per_page'];
				if(isset($options['url'])){
					$this->url = $options['url'];
				}elseif(isset($options['append'])){
					$this->append = $options['append'];
				}
			}
			$this->last_page = ceil($this->total / $this->per_page);
		}
		
		public static function make($results, $page = 1, $options = array()){
			return new self($results, $page, $options);
		}
		
		/**
		 * Return results for current page
		 */
		
		public function results(){
			$offset = ($this->page == 1) ? 0 : (($this->page - 1) * $this->per_page) - 1;
			return array_slice($this->results, $offset, $this->per_page);
		}
		
		public function navigation($options = array()){
			$defaults = array(
				'first_last' => true,
				'prev_next' => true,
				'numbers' => true,
				'padding' => 5,
				'prev_text' => 'Previous',
				'next_text' => 'Next',
				'first_text' => 'First',
				'last_text' => 'Last'
			);
			$options = $options + $defaults;
			
			$nav = '';
			
			if($options['numbers']){
				$first = $this->page - $options['padding'];
				$last = $this->page + $options['padding'];
				
				if($first > 2) $nav .= $this->beginning();
				$nav .= $this->range($first, $last);
				if($last < $this->last_page - 1) $nav .= $this->ending();
			}
			
			if($options['prev_next']){
				$nav = $this->previous($options['prev_text']) . $nav;
				$nav .= $this->next($options['next_text']);
			}
			
			if($options['first_last']){
				$nav = $this->first($options['first_text']) . $nav;
				$nav .= $this->last($options['last_text']);
			}
			
			return '<div class="pagination">'.$nav.'</div>';
		}
		
		public function beginning(){
			return $this->range(1, 2) . '<span class="ellipsis">...</span> ';
		}
		
		public function ending(){
			return '<span class="ellipsis">...</span> ' . $this->range($this->last_page - 1, $this->last_page) . ' ';
		}
		
		public function previous($text = 'Previous'){
			if($this->page > 1) return $this->link($this->page - 1, $text, 'prev_page') . ' ';
			return HTML::span($text, array('class' => 'disabled prev_page')) . ' ';
		}
		
		public function next($text = 'Next'){
			if($this->page < $this->last_page) return $this->link($this->page + 1, $text, 'next_page') . ' ';
			return HTML::span($text, array('class' => 'disabled next_page')) . ' ';
		}
		
		public function first($text = 'First'){
			if($this->page != 1) return $this->link(1, $text, 'first_page') . ' ';
			return HTML::span($text, array('class' => 'disabled first_page')) . ' ';
		}
		
		public function last($text = 'Last'){
			if($this->page != $this->last_page) return $this->link($this->last_page, $text, 'last_page') . ' ';
			return HTML::span($text, array('class' => 'disabled last_page')) . ' ';
		}
		
		public function range($start, $end){
			if($start < 1) $start = 1;
			if($end > $this->last_page) $end = $this->last_page;
			$pages = '';
			for($i = $start; $i <= $end; $i++){
				$pages .= ($i == $this->page) ? HTML::span($i, array('class' => 'current')).' ' : $this->link($i, $i, null).' ';
			}
			return $pages;
		}
		
		private function link($page, $text, $class = null){
			if($this->url !== false){
				$url = $this->url;
				if(!preg_match('/^\//', $url)) $url = '/' . $url;
				$link = Config::get('site.url').$url.$page;
			}else{
				$link = App::request().'?page='.$page;
				if(!empty($this->append)){
					foreach($this->append as $key => $value){
						$link .= '&'.$key.'='.$value;
					}
				}
			}
			return HTML::link($link, $text, compact('class'));
		}
	
	}