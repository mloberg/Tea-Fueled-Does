<p>Hello World!</p>

<p>Rendered in {time} seconds using {memory}mb of memory.</p>
<?php
$profile = $this->profile();
$replace = array('{time}' => $profile[0], '{memory}' => $profile[1]);
?>