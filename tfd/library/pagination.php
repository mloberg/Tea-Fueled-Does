<?php

	class Pagination extends App{
	
		static private $config = array('per_page' => 10);
		static private $results = null;
		
		function config($array){
			self::$config = $array + self::$config;
		}
		
		function sql($sql){
			$this->mysql->qry($sql);
			self::$config['num_items'] = $this->mysql->num_rows();
			$page = ($_GET['page']) ? $_GET['page'] - 1 : 0;
			$offset = $page * self::$config['per_page'];
			$qry = sprintf("%s LIMIT %u , %u",
				$sql, $offset, self::$config['per_page']);
			self::$results = $this->mysql->qry($qry, true);
		}
		
		function template($tmpl){
			self::$config['template'] = str_replace('"', '\\"', $tmpl);
		}
		
		function content(){
			foreach(self::$results as $row){
				$tmpl = preg_replace('/{(\w*)}/', '{$row[\'$1\']}', self::$config['template']);
				eval("\$str .= \"$tmpl\";");
			}
			return $str;
		}
		
		function navigation(){
			$pages = ceil(self::$config['num_items'] / self::$config['per_page']);
			$next_page = ($_GET['page']) ? $_GET['page'] + 1 : 2;
			if($next_page !== 2){
				$previous_page = $_GET['page'] - 1;
				$str = '<a href="?page='.$previous_page.'">Previous</a> ';
			}
			$page = ($_GET['page']) ? $_GET['page'] : 1;
			if($pages != $page){
				$str .= '<a href="?page='.$next_page.'">Next</a>';
			}
			return $str;
		}
	
	}