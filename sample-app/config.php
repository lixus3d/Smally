<?php

/* SMALLY CONFIG */
$config['smally']['paths']['assets'][]		= 'http://assets.mysmally.com/';
$config['smally']['paths']['assets'][]		= 'http://assets1.mysmally.com/';
$config['smally']['paths']['assets'][]		= 'http://assets2.mysmally.com/';

$config['smally']['db']['host']				= 'localhost';
$config['smally']['db']['username']			= 'root';
$config['smally']['db']['password']			= 'password';
$config['smally']['db']['database']			= 'database';

$config['smally']['logger']['level']['default'] = 100 ;
$config['smally']['logger']['level']['dao']  	= \Smally\Logger::LVL_ERROR ;
$config['smally']['logger']['level']['rooter']  = \Smally\Logger::LVL_ERROR ;

$config['smally']['default']['paging']['limit'] = 10;
$config['smally']['default']['paging']['urlParam'] = 'page';


if(\Smally\Application::getInstance()->isDev()){
	/* PLACE YOUR DEVELOPMENT SPECIAL CONFIG HERE */
	//$config['x']['y'] 				= 'z';
	$config['smally']['logger']['level']['dao']  	= \Smally\Logger::LVL_INFO ;
	$config['smally']['logger']['level']['rooter']  = \Smally\Logger::LVL_INFO ;
}


if(\Smally\Application::getInstance()->getEnvironnement() == \Smally\Application::ENV_STAGING){
	/* PLACE YOUR STAGING SPECIAL CONFIG HERE */
	//$config['x']['y'] 				= 'z';
}