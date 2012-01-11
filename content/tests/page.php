<?php namespace Content\Tests;

	use TFD\Test;

	/**
	 * Tests for TFD\Test\Page which test page content and headers
	 * Some of these test are written so that they fail
	 */

	class Page extends Test{
		
		const name = 'TFD\Test\Page';

		private static $index;

		public function __construct(){
			self::$index = $this->page('/index');
		}

		public function test_is_status(){
			self::$index->assertStatusIs(200);
			self::$index->assertStatusIs(404); // expected
		}

		public function test_not_status(){
			self::$index->assertStatusNot(200); // expected
			self::$index->assertStatusNot(404);
		}

		public function test_content(){
			self::$index->assertContent();
			self::$index->assertContentEmpty(); // expected
		}

		public function test_in_content(){
			self::$index->assertInContent('Hello World');
			self::$index->assertNotInContent('Hello World'); // expected
		}

		public function test_header(){
			self::$index->assertHeaderExists('Content-Length');
			self::$index->assertHeaderNotExists('Content-Length'); // expected
			self::$index->assertHeaderIs('Content-Type', 'text/html; charset=utf-8');
			self::$index->assertHeaderNot('Content-Type', 'text/html; charset=utf-8'); // expected
		}

	}