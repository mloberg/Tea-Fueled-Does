<?php
	$passes = $fails = $exceptions = $completed = 0;
	$total = count($results);
?>
<h1><?php echo ucwords($name);?> Tests</h1>

<?php foreach($results as $test_name => $result):
	$passed = array_filter($result, function($test){
		return($test['result'] == 'passed');
	});
	$failed = array_filter($result, function($test){
		return($test['result'] == 'failed');
	});
	$exception = array_filter($result, function($test){
		return($test['result'] == 'exception');
	});
	$class = (!empty($failed) || !empty($exception)) ? 'failed' : 'passed';
	if(empty($exception)) $completed++;
?>
<h3 class="<?php echo $class;?>"><?php echo $test_name;?></h3>
<ul>
	<?php foreach($result as $test):
		if($test['result'] == 'passed') $passes++;
		if($test['result'] == 'failed') $fails++;
		if($test['result'] == 'exception') $exceptions++;
		if(($show_passed && $test['result'] == 'passed') || $test['result'] == 'failed'):
	?>
	<li>
		<span class="<?php echo $test['result'];?>"><?php echo ucwords($test['result']);?></span>:
		<?php echo $test['test'];?>
		<?php if($test['result'] == 'failed' && !is_null($test['message'])) echo '('.$test['message'].')';?>
		[at <?php echo $test['file'];?> line <?php echo $test['line'];?>]
	</li>
	<?php elseif($test['result'] == 'exception'):?>
	<li>
		<span class="exception">Exception</span>: (<?php echo $test['message'];?>)
		[at <?php echo $test['file'];?> line <?php echo $test['line'];?>]
	</li>
	<?php
		endif;
	endforeach;?>
</ul>
<?php endforeach;?>
<?php
$flash_message = sprintf("%s/%s test cases complete. %s passes, %s fails, %s exceptions.", $completed, $total, $passes, $fails, $exceptions);
if($fails !== 0 || $exceptions !== 0){
	Flash::error($flash_message, array('sticky' => true));
}else{
	Flash::success($flash_message, array('sticky' => true));
}
CSS::style(array(
	'#wrapper' => array(
		'margin' => '20px'
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
	'.passed, .failed, .exception' => array(
		'color' => 'red',
		'font-weight' => 'bold'
	),
	'.passed' => array(
		'color' => 'green'
	)
));