<?php

	$image = PUBLIC_DIR.'placeholder_thumb.jpg';
	
	$i = new Image('placeholder_thumb.jpg');
	
	try{
		print_r($i->scale()->save('', 'place'));
	}catch(Exception $e){
		echo $e->getMessage();
	}