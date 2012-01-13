<?php namespace Content\Tests;

	use TFD\Test;

	/**
	 * Tests for TFD\Test\Page which test page content and headers
	 * Some of these test are written so that they fail
	 */

	class Page extends Test{
		
		const name = 'TFD\Test\Page';

		private static $index;
		private static $redirect;
		private static $post;
		private static $admin;

		public function __construct(){
			self::$index = Test\Page::make('/index');
			self::$redirect = $this->page('/redirect');
			$post = array(
				'method' => 'post',
				'post_data' => array(
					'foo' => 'bar'
				)
			);
			self::$post = $this->page('/post', $post);
			self::$admin = $this->page('/admin/index', array('admin' => true, 'username' => 1));
		}

		public function test_status(){
			self::$index->assertStatusIs(200);
			self::$index->assertStatusIs(404, 'Expected');
			self::$index->assertStatusNot(200, 'Expected');
			self::$index->assertStatusNot(404);
		}

		public function test_content(){
			self::$index->assertContent();
			self::$index->assertContentEmpty('Expected');
			self::$index->assertInContent('Hello World');
			self::$index->assertNotInContent('Hello World', 'Expected');
		}

		public function test_header(){
			self::$index->assertHeaderExists('Content-Length');
			self::$index->assertHeaderNotExists('Content-Length', 'Expected');
			self::$index->assertHeaderIs('Content-Type', 'text/html; charset=utf-8');
			self::$index->assertHeaderNot('Content-Type', 'text/html; charset=utf-8', 'Expected');
		}

		public function test_content_type(){
			self::$index->assertContentType('text/html');
			self::$index->assertContentTypeNot('text/html', 'Expected');
		}

		public function test_redirects(){
			self::$redirect->assertRedirect();
			self::$index->assertRedirect('Expected');
			self::$index->assertNotRedirect();
			self::$redirect->assertNotRedirect('Expected');
		}

		public function test_post(){
			self::$post->assertInContent('bar');
			self::$post->assertStatusIs(302, 'Expected');
		}

		public function test_admin(){
			// if there is no user with an id of 1, the expected is reversed
			self::$admin->assertInContent('Hello Dashboard');
			self::$admin->assertStatusIs(302, 'Expected');
		}

	}