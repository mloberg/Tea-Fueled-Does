<?php

$content = array(
	'name' => 'World',
	'content' => 'foobar'
);

$html = Render::template('foo.stache', $content);

echo $html;