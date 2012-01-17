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
	use TFD\Image;
	use TFD\JavaScript;
	use TFD\Loader;
	use TFD\Model;
	use TFD\Paginator;
	use TFD\Postmark;
	use TFD\ReCAPTCHA;
	use TFD\Redis;
	use TFD\RSS;
	use TFD\Template;
	use TFD\Core\Render;
	use TFD\Core\Request;
	use TFD\Core\Response;
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
			@unlink($file);
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
			$ch = curl_init('http://placehold.it/174.jpg');
			$img = BASE_DIR.'cache/foo.jpg';
			$fp = fopen($img, 'wb');
			curl_setopt($ch, CURLOPT_FILE, $fp);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_exec($ch);
			curl_close($ch);
			fclose($fp);
			self::assertException(function(){
				Image::make($img)->resize('string', 'string');
			});
			self::assertException(function(){
				Image::make($img)->scale('string');
			});
			self::assertException(function(){
				Image::make($img)->scale(array('width' => 175));
			});
			self::assertException(function(){
				Image::make($img)->crop('string', 'string');
			});
			$image = Image::make($img);
			self::assertIsA($image->rotate('right'), 'TFD\Image');
			$image->resize(150, 150);
			$image->scale(array('percent' => 50));
			$image->crop(75, 75, 10, 10);
			$image->watermark($img);
			self::assertTrue($image->save(BASE_DIR.'cache', 'bar'));
			list($width, $height) = getimagesize(BASE_DIR.'cache/bar.jpg');
			self::assert(($width === 75 && $height === 75), 'Unexpected image size');
			list($width, $height) = getimagesize($img);
			self::assert(($width === 174 && $height === 174), 'Unexpected image size');
			@unlink(BASE_DIR.'cache/bar.jpg');
			@unlink($img);
		}

		public function test_javascript(){
			JavaScript::update_library('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js', true);
			JavaScript::library('tfd');
			JavaScript::script('function(){ alert("hello");}');
			JavaScript::ready('function(){alert("world");}');
			$render = JavaScript::render();
			self::assertPattern($render, '/googleapis/');
			self::assertPattern($render, '/tfd\.js/');
			self::assertPattern($render, '/hello/');
			self::assertPattern($render, '/world/');
		}

		public function test_loader(){
			self::assertException(function(){
				new \TFD\No\File;
			});
		}

		public function test_model(){
			self::assertException(function(){
				Model::make();
			});
			self::assertEqual(Model::make('model')->foo(), 'foobar');
		}

		public function test_paginator(){
			$results = Test\Fixture::load('tfd')->paginator;
			$paginator = Paginator::make($results, 2, array('per_page' => 3));
			self::assertIsA($paginator, 'TFD\Paginator');
			self::assert((count($paginator->results()) === 3), 'Expected 3 results');
			self::assertNotEmpty($paginator->navigation());
		}

		public function test_postmark(){
			self::assertException(function(){
				Postmark::make()->send();
			});
			$postmark = Postmark::make();
			$postmark->to('mail@example.com')->cc('cc@example.com')->bcc('bcc@example.com');
			$postmark->subject('Postmark Test')->message('Lorem Ipsum')->tag('test');
			$resp = $postmark->send();
			self::assertFalse($resp->sent());
		}

		public function test_recaptcha(){
			self::assertException(function(){
				ReCAPTCHA::get_html();
			});
		}

		public function test_redis(){
			$redis = Redis::make(array('hostname' => '127.0.0.1', 'port' => 6379));
			$redis->set('mykey', 'Hello');
			self::assertEqual($redis->get('mykey'), 'Hello');
			self::assertEqual($redis->del('mykey'), '1');
		}

		public function test_rss(){
			$rss = RSS::make(array('title' => 'Test', 'link' => '/'));
			$rss->description = 'RSS Test';
			$item = RSS::item(array('title' => 'Item 1', 'link' => '/item/1'));
			$item->description = 'RSS test item 1';
			$rss->add($item);
			$rss->add(array(
				'title' => 'Item 2',
				'link' => '/item/2',
				'description' => 'RSS test item 2'
			));
			self::assertNotEmpty((string)$rss);
		}

		public function test_template(){
			self::assertEqual(Template::make('hello {{name}}', array('name' => 'world')), 'hello world');
		}

		public function test_render(){
			self::assertIsA(Render::page(array()), 'TFD\Core\Page');
			self::assertIsA(Render::view(array()), 'TFD\Core\View');
			self::assertIsA(Render::partial('foo'), 'TFD\Core\View');
			self::assertIsA(Render::error('404'), 'TFD\Core\ErrorPage');
			self::assertNotEmpty(Render::partial('test'));
		}

		public function test_request(){
			$request = new Request('/foo');
			self::assertFalse($request->run());
			self::assertFalse(Request::is_maintenance());
			self::assertFalse(Request::is_ajax());
			self::assertType(Request::spoofed(), 'boolean');
			if(!self::is_cli()){
				self::assertEqual(Request::uri(), '/foo');
				self::assertNotEmpty(Request::method());
				self::assertNotEmpty(Request::ip());
			}
			self::assertPattern(Request::protocol(), '/http/');
			self::assertType(Request::is_secure(), 'boolean');
		}

		public function test_response(){
			self::assertIsA(Response::make('', 200), 'TFD\Core\Response');
			self::assertIsA(Response::error(500), 'TFD\Core\Response');
		}

		public function test_mysql(){
			$fixture = Test\Fixture::load('tfd');
			self::assertIsA(MySQL::connection(), 'PDO');
			$table = uniqid();
			self::assertTrue(MySQL::query("CREATE TABLE `{$table}` (`id` int(11) unsigned NOT NULL AUTO_INCREMENT,`title` varchar(128) DEFAULT NULL,`content` text,PRIMARY KEY (`id`))"));
			self::assertTrue(MySQL::table($table)->insert($fixture->mysql_insert));
			self::assertEqual(MySQL::insert_id(), '1');
			self::assertTrue(MySQL::table($table)->insert($fixture->mysql_multi_insert));
			self::assertTrue(MySQL::table($table)->where('title', '=', 'bar')->and_where('content', '=', 'foo')->update(array('content' => 'Lorem Ipsum')));
			self::assertTrue(MySQL::table($table)->where('title', 'LIKE', 'foo%')->or_where('content', '=', 'Lorem Ipsum')->set('content', 'new content'));
			$results = MySQL::table($table)->get();
			self::assertType($results, 'array');
			self::assertEqual(MySQL::num_rows(), '3');
			$test = true;
			foreach($results as $result){
				if($result['content'] !== 'new content') $test = false;
			}
			self::assert($test, 'Content was not updated as expected');
			self::assert((count(MySQL::table($table)->limit(2)->get()) === 2), 'Unexpected number of results');
			self::assertTrue(MySQL::table($table)->where('title', '=', 'foobar')->delete());
			self::assert((count(MySQL::table($table)->get()) === 2), 'Unexpected number of results');
			self::assertTrue(MySQL::table($table)->delete(true));
			self::assertEmpty(MySQL::table($table)->get());
			self::assertTrue(MySQL::query("DROP TABLE `{$table}"));
		}

	}