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

);