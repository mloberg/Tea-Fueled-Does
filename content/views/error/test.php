<?php
	$tests_passed = $tests_failed = $passes = $fails = 0;
	$total = count($results);
?>
<h1><?php echo ucwords($name);?> Tests</h1>

<?php foreach($results as $test_name => $result):?>
<h3><?php echo $test_name;?></h3>
<ul>
	<?php foreach($result as $test):
		if($test['result'] == 'passed') $passes++;
		if($test['result'] == 'failed') $fails++;
	?>
	<li>
		<span class="<?php echo $test['result'];?>"><?php echo ucwords($test['result']);?></span>: <?php echo $test['test'];?> [at <?php echo $test['file'];?> line <?php echo $test['line'];?>]
	</li>
	<?php endforeach;?>
</ul>
<?php endforeach;?>
<?php
Flash::message(sprintf("%s passes, %s fails", $passes, $fails));
CSS::style(array(
	'#wrapper' => array(
		'padding' => '10px'
	),
	'h1' => array(
		'font-size' => '28px',
		'margin-bottom' => '10px'
	),
	'h3' => array(
		'font-size' => '20px',
		'margin' => '10px 0'
	),
	'ul' => array(
		'margin-left' => '10px'
	),
	'.passed, .failed' => array(
		'color' => 'red',
		'font-weight' => 'bold'
	),
	'.passed' => array(
		'color' => 'green'
	)
));