<?php

$script = <<<SCRIPT
$.ajax({
	type: 'get',
	url: 'ajax/page',
	success: function(msg){
		console.log(msg);
	}
});
SCRIPT;

JavaScript::library('jquery');
JavaScript::ready($script);