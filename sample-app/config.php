<?php

/* SMALLY CONFIG */
$config['smally']['paths']['assets'][]		= 'http://assets.mysmally.com/';
$config['smally']['paths']['assets'][]		= 'http://assets1.mysmally.com/';
$config['smally']['paths']['assets'][]		= 'http://assets2.mysmally.com/';


if(\Smally\Application::getInstance()->isDev()){
	/* PLACE YOUR DEVELOPMENT SPECIAL CONFIG HERE */
	//$config['x']['y'] 				= 'z';
}


if(\Smally\Application::getInstance()->getEnvironnement() == \Smally\Application::ENV_STAGING){
	/* PLACE YOUR STAGING SPECIAL CONFIG HERE */
	//$config['x']['y'] 				= 'z';
}