<?php

	$this->pagination->sql("SELECT * FROM posts");
	$tmpl = <<<TMPL
<div class="post">
	<h3>{title}</h3>
	<p>{body}</p>
</div>

TMPL;
	$this->pagination->template($tmpl);
	echo $this->pagination->content();
	echo $this->pagination->navigation();