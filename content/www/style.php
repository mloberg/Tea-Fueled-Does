<?php
	$style = array(
		'#element' => array(
			'border-radius' => '5px',
			'drop-shadow' => array(
				'color' => '#000',
				'spread' => '3px',
				'blur' => '5px'
			)
		),
		'.class' => array(
			'color' => '#999',
			'background-color' => '#000'
		)
	);
	
	$this->css->style($style);
?>
<div id="element" class="class">
	<p>Test</p>
</div>