<?php $render->title = 'Uncaught Exception';?>
<style type="text/css">
	p{margin: 10px 0 0 0;line-height: 25px;}
	pre{font-size: 12px;}
	pre.context{margin: 0; padding: 0;}
	pre.highlight{font-weight: bold;color: #990000;}
</style>
<h1><?php echo $severity;?></h1>
<h2>Message:</h2>
<p><?php echo $message;?></p>

<h2>Stack Trace:</h2>
<pre><?php echo $trace;?></pre>

<h2>Snapshot:</h2>
<p>
<?php
	if(count($contexts) > 0):
		foreach($contexts as $num => $context):
?>
		<pre class="context <?php echo ($line == $num) ? 'highlight' : '';?>"><?php echo htmlentities($num.': '.$context);?></pre>
<?php
		endforeach;
	else:
?>
	Snapshot Unavailable.
<?php endif;?>
</p>