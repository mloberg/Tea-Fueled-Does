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

		public function test_admin(){
			if(Admin::loggedin()){
				Admin::logout();
			}
			Test\Assert::type(Admin::loggedin(), 'boolean');
			$user = $pass = uniqid();
			Test\Assert::false(Admin::validate($user, $pass));
			Test\Assert::true(Admin::add_user($user, $pass));
			Test\Assert::true(Admin::validate_user_pass($user, $pass));
			Test\Assert::true(Admin::validate($user, $pass));
			Test\Assert::true(Admin::validate_pass($pass));
			Test\Assert::null(Admin::logout());
			// delete the created user
			MySQL::table(Config::get('admin.table'))->where('username', '=', $user)->delete();
		}

		public function test_benchmark(){
			Test\Assert::null(Benchmark::start('foo'));
			Test\Assert::type(Benchmark::check('foo'), 'double');
			Test\Assert::equal(Benchmark::check('bar'), 0.0);
			Test\Assert::null(Benchmark::memory('foo'));
			Test\Assert::type(Benchmark::used_memory('foo'), 'double');
			Test\Assert::equal(Benchmark::used_memory('bar'), 0.0);
			Test\Assert::type(Benchmark::current_memory(), 'double');
			Test\Assert::type(Benchmark::peak_memory(), 'double');
		}

		public function test_cache(){
			Config::set('cache.driver', 'file');
			Test\Assert::isA(Cache::driver('file'), 'TFD\Cache\File');
			Test\Assert::false(Cache::has('foo'));
			Test\Assert::null(Cache::get('foo'));
			Test\Assert::equal(Cache::store('foo', 'bar', 60), 'bar');
			Test\Assert::true(Cache::has('foo'));
			Test\Assert::equal(Cache::remember('foo', null, 60), 'bar');
			Test\Assert::true(Cache::clear('foo'));
		}

		public function test_config(){
			Test\Assert::false(Config::is_set('test.foo'));
			Test\Assert::exception(function(){
				Config::set('foo.bar.foo', 'bar');
			});
			Test\Assert::equal(Config::set('test.foo', 'bar'), 'bar');
			Test\Assert::exception(function(){
				Config::load('foo');
			});
			Test\Assert::null(Config::load(array('test.bar' => 'foobar')));
			Test\Assert::true(Config::is_set('test.bar'));
			Test\Assert::equal(Config::get('test.bar'), 'foobar');
		}

		public function test_crypter(){
			$string = 'foobar';
			$salt = uniqid();
			Test\Assert::notEmpty(($hash = Crypter::hash($string)));
			Test\Assert::true(Crypter::verify($string, $hash));
			Test\Assert::false(Crypter::verify($string, $string));
			Test\Assert::notEmpty(Crypter::hash_with_salt($string, $salt));
		}

		public function test_css(){
			Test\Assert::null(CSS::style(array('foo' => array('color' => 'black'))));
			Test\Assert::null(CSS::load('reset'));
			Test\Assert::pattern(CSS::render(), '/reset/');
		}

		public function test_file(){
			$file = BASE_DIR.'cache/tmp';
			File::put($file, 'foo');
			File::append($file, 'bar');
			Test\Assert::equal(File::get($file), 'foobar');
			Test\Assert::type(File::snapshot('non-existant-file', 5), 'array');
			Test\Assert::type(File::snapshot($file, 1), 'array');
			@unlink($file);
		}

		public function test_form(){
			$atr = array('class' => 'test');
			Test\Assert::pattern(Form::open('/post', 'POST', $atr), '/form/');
			Test\Assert::pattern(Form::open_upload('/post', 'POST', $atr), '/form/');
			Test\Assert::pattern(Form::close(), '/\/form/');
			Test\Assert::pattern(Form::label('foo', 'bar', $atr), '/label/');
			Test\Assert::pattern(Form::input('text', 'foo', 'bar', $atr), '/input/');
			Test\Assert::pattern(Form::text('foo', 'bar', $atr), '/text/');
			Test\Assert::pattern(Form::password('foo', 'bar', $atr), '/password/');
			Test\Assert::pattern(Form::hidden('foo', 'bar', $atr), '/hidden/');
			Test\Assert::pattern(Form::file('foo', $atr), '/file/');
			Test\Assert::pattern(Form::textarea('foo', 'bar', $atr), '/textarea/');
			Test\Assert::pattern(Form::select('foo', array('foo', 'bar'), 'foo', $atr), '/select/');
			Test\Assert::pattern(Form::checkbox('foo', 'bar', true, $atr), '/checkbox/');
			Test\Assert::pattern(Form::radio('foo', 'bar', true, $atr), '/radio/');
			Test\Assert::pattern(Form::submit('go', $atr), '/submit/');
		}

		public function test_html(){
			$atr = array('class' => 'test');
			Test\Assert::notEmpty(HTML::obfuscate('foobar'));
			Test\Assert::notEmpty(HTML::entities('<div id="foo">bar</div>'));
			Test\Assert::notEmpty(HTML::attributes($atr));
			$methods = array('div', 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6');
			foreach($methods as $method){
				Test\Assert::pattern(HTML::$method('foo', $atr, true), '/foo/');
			}
			Test\Assert::pattern(HTML::link('/foo', 'Foo', $atr), '/foo/');
			Test\Assert::pattern(HTML::mailto('mail@example.com', 'foo', $arg), '/foo/');
			Test\Assert::pattern(HTML::rss_link('/rss', 'foo', $atr), '/foo/');
			Test\Assert::pattern(HTML::image('http://placehold.it/5', 'foo', $atr), '/foo/');
			Test\Assert::exception(function(){
				HTML::ul('string');
			});
			Test\Assert::pattern(HTML::ul(array('foo', 'bar'), $atr, true), '/foo/');
			Test\Assert::exception(function(){
				HTML::ol('string');
			});
			Test\Assert::pattern(HTML::ol(array('foo', 'bar'), $atr, true), '/foo/');
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
			Test\Assert::exception(function(){
				Image::make($img)->resize('string', 'string');
			});
			Test\Assert::exception(function(){
				Image::make($img)->scale('string');
			});
			Test\Assert::exception(function(){
				Image::make($img)->scale(array('width' => 175));
			});
			Test\Assert::exception(function(){
				Image::make($img)->crop('string', 'string');
			});
			$image = Image::make($img);
			Test\Assert::isA($image->rotate('right'), 'TFD\Image');
			$image->resize(150, 150);
			$image->scale(array('percent' => 50));
			$image->crop(75, 75, 10, 10);
			$image->watermark($img);
			Test\Assert::true($image->save(BASE_DIR.'cache', 'bar'));
			list($width, $height) = getimagesize(BASE_DIR.'cache/bar.jpg');
			Test\Assert::true(($width === 75 && $height === 75), 'Unexpected image size');
			list($width, $height) = getimagesize($img);
			Test\Assert::true(($width === 174 && $height === 174), 'Unexpected image size');
			@unlink(BASE_DIR.'cache/bar.jpg');
			@unlink($img);
		}

		public function test_javascript(){
			JavaScript::update_library('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js', true);
			JavaScript::library('tfd');
			JavaScript::script('function(){ alert("hello");}');
			JavaScript::ready('function(){alert("world");}');
			$render = JavaScript::render();
			Test\Assert::pattern($render, '/googleapis/');
			Test\Assert::pattern($render, '/tfd\.js/');
			Test\Assert::pattern($render, '/hello/');
			Test\Assert::pattern($render, '/world/');
		}

		public function test_loader(){
			Test\Assert::exception(function(){
				new \TFD\No\File;
			});
		}

		public function test_model(){
			Test\Assert::exception(function(){
				Model::make();
			});
			Test\Assert::equal(Model::make('model')->foo(), 'foobar');
		}

		public function test_paginator(){
			$results = Test\Fixture::load('tfd')->paginator;
			$paginator = Paginator::make($results, 2, array('per_page' => 3));
			Test\Assert::isA($paginator, 'TFD\Paginator');
			Test\Assert::true((count($paginator->results()) === 3), 'Expected 3 results');
			Test\Assert::notEmpty($paginator->navigation());
		}

		public function test_postmark(){
			Test\Assert::exception(function(){
				Postmark::make()->send();
			});
			$postmark = Postmark::make();
			$postmark->to('mail@example.com')->cc('cc@example.com')->bcc('bcc@example.com');
			$postmark->subject('Postmark Test')->message('Lorem Ipsum')->tag('test');
			$resp = $postmark->send();
			Test\Assert::false($resp->sent());
		}

		public function test_recaptcha(){
			Test\Assert::exception(function(){
				ReCAPTCHA::get_html();
			});
		}

		public function test_redis(){
			$redis = Redis::make(array('hostname' => '127.0.0.1', 'port' => 6379));
			$redis->set('mykey', 'Hello');
			Test\Assert::equal($redis->get('mykey'), 'Hello');
			Test\Assert::equal($redis->del('mykey'), '1');
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
			Test\Assert::notEmpty((string)$rss);
		}

		public function test_template(){
			Test\Assert::equal(Template::make('hello {{name}}', array('name' => 'world')), 'hello world');
		}

		public function test_render(){
			Test\Assert::isA(Render::page(array()), 'TFD\Core\Page');
			Test\Assert::isA(Render::view(array()), 'TFD\Core\View');
			Test\Assert::isA(Render::partial('foo'), 'TFD\Core\View');
			Test\Assert::isA(Render::error('404'), 'TFD\Core\ErrorPage');
			Test\Assert::notEmpty(Render::partial('test'));
		}

		public function test_request(){
			$request = new Request('/foo');
			Test\Assert::false($request->run());
			Test\Assert::false(Request::is_maintenance());
			Test\Assert::false(Request::is_ajax());
			Test\Assert::type(Request::spoofed(), 'boolean');
			if(!self::is_cli()){
				Test\Assert::equal(Request::uri(), '/foo');
				Test\Assert::notEmpty(Request::method());
				Test\Assert::notEmpty(Request::ip());
			}
			Test\Assert::pattern(Request::protocol(), '/http/');
			Test\Assert::type(Request::is_secure(), 'boolean');
		}

		public function test_response(){
			Test\Assert::isA(Response::make('', 200), 'TFD\Core\Response');
			Test\Assert::isA(Response::error(500), 'TFD\Core\Response');
		}

		public function test_mysql(){
			$fixture = Test\Fixture::load('tfd');
			Test\Assert::isA(MySQL::connection(), 'PDO');
			$table = uniqid();
			Test\Assert::true(MySQL::query("CREATE TABLE `{$table}` (`id` int(11) unsigned NOT NULL AUTO_INCREMENT,`title` varchar(128) DEFAULT NULL,`content` text,PRIMARY KEY (`id`))"));
			Test\Assert::true(MySQL::table($table)->insert($fixture->mysql_insert));
			Test\Assert::equal(MySQL::insert_id(), '1');
			Test\Assert::true(MySQL::table($table)->insert($fixture->mysql_multi_insert));
			Test\Assert::true(MySQL::table($table)->where('title', '=', 'bar')->and_where('content', '=', 'foo')->update(array('content' => 'Lorem Ipsum')));
			Test\Assert::true(MySQL::table($table)->where('title', 'LIKE', 'foo%')->or_where('content', '=', 'Lorem Ipsum')->set('content', 'new content'));
			$results = MySQL::table($table)->get();
			Test\Assert::type($results, 'array');
			Test\Assert::equal(MySQL::num_rows(), '3');
			$test = true;
			foreach($results as $result){
				if($result['content'] !== 'new content') $test = false;
			}
			Test\Assert::true($test, 'Content was not updated as expected');
			Test\Assert::true((count(MySQL::table($table)->limit(2)->get()) === 2), 'Unexpected number of results');
			Test\Assert::true(MySQL::table($table)->where('title', '=', 'foobar')->delete());
			Test\Assert::true((count(MySQL::table($table)->get()) === 2), 'Unexpected number of results');
			Test\Assert::true(MySQL::table($table)->delete(true));
			Test\Assert::isEmpty(MySQL::table($table)->get());
			Test\Assert::true(MySQL::query("DROP TABLE `{$table}"));
		}

	}