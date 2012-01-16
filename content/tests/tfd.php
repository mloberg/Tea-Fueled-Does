<?php namespace Content\Tests;

	use TFD\Test;

	/**
	 * Tests for TFD classes
	 */

	use TFD\Admin;
	use TFD\Benchmark;
	use TFD\Cache;
	use TFD\Config;
	use TFD\Crypter;
	use TFD\CSS;
	use TFD\File;
	use TFD\Form;
	use TFD\HTML;
	use TFD\DB\MySQL;

	class TFD extends Test{
		
		const name = 'TFD';

		// public function test_admin(){
		// 	if(Admin::loggedin()){
		// 		Admin::logout();
		// 	}
		// 	self::assertType(Admin::loggedin(), 'boolean');
		// 	$user = $pass = uniqid();
		// 	self::assertFalse(Admin::validate($user, $pass));
		// 	self::assertTrue(Admin::add_user($user, $pass));
		// 	self::assertTrue(Admin::validate_user_pass($user, $pass));
		// 	self::assertTrue(Admin::validate($user, $pass));
		// 	self::assertTrue(Admin::validate_pass($pass));
		// 	self::assertNull(Admin::logout());
		// 	// delete the created user
		// 	MySQL::table(Config::get('admin.table'))->where('username', '=', $user)->delete();
		// }

		public function test_benchmark(){
			self::assertNull(Benchmark::start('foo'));
			self::assertType(Benchmark::check('foo'), 'double');
			self::assertEqual(Benchmark::check('bar'), 0.0);
			self::assertNull(Benchmark::memory('foo'));
			self::assertType(Benchmark::used_memory('foo'), 'double');
			self::assertEqual(Benchmark::used_memory('bar'), 0.0);
			self::assertType(Benchmark::current_memory(), 'double');
			self::assertType(Benchmark::peak_memory(), 'double');
		}

		public function test_cache(){
			Config::set('cache.driver', 'file');
			self::assertIsA(Cache::driver('file'), 'TFD\Cache\File');
			self::assertFalse(Cache::has('foo'));
			self::assertNull(Cache::get('foo'));
			self::assertEqual(Cache::store('foo', 'bar', 60), 'bar');
			self::assertTrue(Cache::has('foo'));
			self::assertEqual(Cache::remember('foo', null, 60), 'bar');
			self::assertTrue(Cache::clear('foo'));
		}

		public function test_config(){
			self::assertFalse(Config::is_set('test.foo'));
			self::assertException(function(){
				Config::set('foo.bar.foo', 'bar');
			});
			self::assertEqual(Config::set('test.foo', 'bar'), 'bar');
			self::assertException(function(){
				Config::load('foo');
			});
			self::assertNull(Config::load(array('test.bar' => 'foobar')));
			self::assertTrue(Config::is_set('test.bar'));
			self::assertEqual(Config::get('test.bar'), 'foobar');
		}

		public function test_crypter(){
			$string = 'foobar';
			$salt = uniqid();
			self::assertNotEmpty(($hash = Crypter::hash($string)));
			self::assertTrue(Crypter::verify($string, $hash));
			self::assertFalse(Crypter::verify($string, $string));
			self::assertNotEmpty(Crypter::hash_with_salt($string, $salt));
		}

		public function test_css(){
			self::assertNull(CSS::style(array('foo' => array('color' => 'black'))));
			self::assertNull(CSS::load('reset'));
			self::assertPattern(CSS::render(), '/reset/');
		}

		public function test_file(){
			$file = BASE_DIR.'cache/tmp';
			File::put($file, 'foo');
			File::append($file, 'bar');
			self::assertEqual(File::get($file), 'foobar');
			self::assertType(File::snapshot('non-existant-file', 5), 'array');
			self::assertType(File::snapshot($file, 1), 'array');
		}

		public function test_form(){
			$atr = array('class' => 'test');
			self::assertPattern(Form::open('/post', 'POST', $atr), '/form/');
			self::assertPattern(Form::open_upload('/post', 'POST', $atr), '/form/');
			self::assertPattern(Form::close(), '/\/form/');
			self::assertPattern(Form::label('foo', 'bar', $atr), '/label/');
			self::assertPattern(Form::input('text', 'foo', 'bar', $atr), '/input/');
			self::assertPattern(Form::text('foo', 'bar', $atr), '/text/');
			self::assertPattern(Form::password('foo', 'bar', $atr), '/password/');
			self::assertPattern(Form::hidden('foo', 'bar', $atr), '/hidden/');
			self::assertPattern(Form::file('foo', $atr), '/file/');
			self::assertPattern(Form::textarea('foo', 'bar', $atr), '/textarea/');
			self::assertPattern(Form::select('foo', array('foo', 'bar'), 'foo', $atr), '/select/');
			self::assertPattern(Form::checkbox('foo', 'bar', true, $atr), '/checkbox/');
			self::assertPattern(Form::radio('foo', 'bar', true, $atr), '/radio/');
			self::assertPattern(Form::submit('go', $atr), '/submit/');
		}

		public function test_html(){
			$atr = array('class' => 'test');
			self::assertNotEmpty(HTML::obfuscate('foobar'));
			self::assertNotEmpty(HTML::entities('<div id="foo">bar</div>'));
			self::assertNotEmpty(HTML::attributes($atr));
			$methods = array('div', 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6');
			foreach($methods as $method){
				self::assertPattern(HTML::$method('foo', $atr, true), '/foo/');
			}
			self::assertPattern(HTML::link('/foo', 'Foo', $atr), '/foo/');
			self::assertPattern(HTML::mailto('mail@example.com', 'foo', $arg), '/foo/');
			self::assertPattern(HTML::rss_link('/rss', 'foo', $atr), '/foo/');
			self::assertPattern(HTML::image('http://placehold.it/5', 'foo', $atr), '/foo/');
			self::assertException(function(){
				HTML::ul('string');
			});
			self::assertPattern(HTML::ul(array('foo', 'bar'), $atr, true), '/foo/');
			self::assertException(function(){
				HTML::ol('string');
			});
			self::assertPattern(HTML::ol(array('foo', 'bar'), $atr, true), '/foo/');
		}

		public function test_image(){
			
		}

	}