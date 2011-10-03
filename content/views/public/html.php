<?php

	echo HTML::div(
		HTML::h1('Hello World').HTML::p(HTML::link('index', 'Home'), array(), false),
		array(),
		false
	);
	
	echo HTML::mailto('mail@example.com');
	
	echo HTML::ul(array(
		HTML::link('index', 'Home'),
		HTML::link('about', 'About')
	), array(), false);
	
	echo PHP_EOL;
	
	echo Form::open(null, 'PUT');
	echo Form::label('type', 'Type');
	echo Form::input('text', 'type');
	echo Form::textarea('description');
	echo Form::input('submit', 'submit');
	echo Form::close();