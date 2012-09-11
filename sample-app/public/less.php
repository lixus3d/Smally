<?php

/**
 * SIMPLE LESS FILE COMPILER
 * In production just call the .less.css static computed file
 */

$path = $_REQUEST['path'];

if($path && (strpos($path,'..')==false) && (strpos($path,'.less') > 0) ){
	$path = './'.$path;
	if(file_exists($path)){
		header('Content-Type: text/css');
		require('../vendors/lessphp/lessc.inc.php');

		$less = new lessc;
		if($env = getenv('PROJECT_ENVIRONNEMENT')){
			if($env == 'production'){
				$less->setFormatter("compressed");
			}
		}
		$less->checkedCompile($path,$path.'.css');
		echo file_get_contents($path.'.css');
		die();
	}
}
header("HTTP/1.0 404 Not Found");
