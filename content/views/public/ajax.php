<div id="response">loading</div>
<?php

$script = <<<SCRIPT
$.ajax({
	type: 'get',
	url: 'ajax/page',
	success: function(msg){
		$("#response").html("Got " + msg + " from /ajax/page");
	}
});
SCRIPT;

JavaScript::library('jquery');
JavaScript::ready($script);