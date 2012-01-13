<?php

/**
 * Here are your routes. You can read more about them at http://teafueleddoes.com/v2/routes
 *
 * If you want to use other classes, make sure you add a use statement before the array.
 */

return array(

	/** Sample Routes
	'GET /form' => function(){
		return array(
			'dir' => 'protected',
			'view' => 'upload',
			'master' => 'custom_master',
			'title' => 'Upload'
		);
	},
	
	'POST /form/post' => function(){
		// do something with the upload
		
		redirect('form');
	}
	**/

	/**
	 * For Tests
	 */

	'GET /tests' => function(){
		return array(
			'master' => 'master',
			'content' => TFD\Test::run('test\tests', true)
		);
	},

	'GET /tests/all' => function(){
		return array(
			'master' => 'master',
			'content' => TFD\Test::run_all(true)
		);
	},

	'GET /redirect' => function(){
		redirect('/index');
	},

	'POST /post' => function(){
		return $_POST['foo'];
	}

);