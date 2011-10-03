<?php

	try{
		MySQL::table('posts')->insert(array('title' => 'new', 'content' => 'Lorem Ipsum.'));
		$id = MySQL::insert_id();
		echo $id;
	}catch(Exception $e){
		echo "Issue inserting to posts: {$e->getMessage()}<br />";
	}
	
	try{
		MySQL::table('posts')->where('id', 28)->update(array('title' => 'New Post'));
	}catch(Exception $e){
		echo "Issue updating post: {$e->getMessage()}<br />";
	}
	
	try{
		$users = MySQL::table('users')->where('username', 'admin')->limit(1)->get();
		print_p($users);
	}catch(Exception $e){
		echo "Issue getting users: {$e->getMessage()}<br />";
	}
	
	try{
		$migrations = MySQL::table('migrations')->order_by(array('active', 'number' => 'ASC'))->get();
		print_p($migrations);
	}catch(Exception $e){
		echo MySQL::last_query();
		echo "Issue getting migrations: {$e->getMessage()}<br />";
	}
	
	try{
		MySQL::table('posts')->update(array('content' => 'test'));
	}catch(Exception $e){
		echo $e->getMessage().'<br />';
	}
	
	$posts = MySQL::table('posts')->get();
	
	print_p($posts);
?>