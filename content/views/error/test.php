<?php
	$passes = $fails = 0;
	$total = count($results);
?>
<h1><?php echo ucwords($name);?> Tests</h1>

<?php foreach($results as $test_name => $result):?>
<h3><?php echo $test_name;?></h3>
<ul>
	<?php foreach($result as $test):
		if($test['result'] == 'passed') $passes++;
		if($test['result'] == 'failed') $fails++;
		if(($show_passed && $test['result'] == 'passed') || $test['result'] == 'failed'):
	?>
	<li>
		<span class="<?php echo $test['result'];?>"><?php echo ucwords($test['result']);?></span>:
		<?php echo $test['test'];?>
		<?php if($test['result'] == 'failed' && !is_null($test['message'])) echo '('.$test['message'].')';?>
		[at <?php echo $test['file'];?> line <?php echo $test['line'];?>]
	</li>
	<?php
		endif;
	endforeach;?>
</ul>
<?php endforeach;?>
<?php
$flash_message = sprintf("%s/%s test cases complete. %s passes, %s fails.", $total, $total, $passes, $fails);
if($fails !== 0){
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
	'.passed, .failed' => array(
		'color' => 'red',
		'font-weight' => 'bold'
	),
	'.passed' => array(
		'color' => 'green'
	)
));