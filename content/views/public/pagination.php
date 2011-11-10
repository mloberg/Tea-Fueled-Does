<?php

$results = Cache::remember('posts', MySQL::table('posts')->get(), 300);

$paginate = \TFD\Paginator::make($results, $_GET['page']);

print_p($paginate->results());

echo $paginate->navigation(array('padding' => 2));