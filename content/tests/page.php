<?php namespace Content\Tests;

	use TFD\Test;

	class Page extends Test{
		
		const name = 'Page';

		public function test_index(){
			$page = $this->page('/index');
			$page->statusIs(200);
		}

	}