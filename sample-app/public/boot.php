<?php

error_reporting(E_ALL); // The project is not ready for production, so report everything for now
ini_set('display_errors',true); // The project is not ready for production, so report everything for now

defined('REAL_PATH') || define('REAL_PATH', realpath(dirname(__FILE__)));
defined('ROOT_PATH') || define('ROOT_PATH','../');
defined('LIBRARY_PATH') || define('LIBRARY_PATH',ROOT_PATH.'../../');
defined('MODEL_PATH') || define('MODEL_PATH',ROOT_PATH.'model/');
defined('VENDORS_PATH') || define('VENDORS_PATH',ROOT_PATH.'vendors/');

defined('BR') || define('BR','<br />');
defined('NN') || define('NN',"\n");
defined('RN') || define('RN',"\r\n");
defined('TT') || define('TT',"\t");

/**
 * Define library path where to search model
 */
$libraryPath = array(
	LIBRARY_PATH,
	MODEL_PATH,
	VENDORS_PATH,
	ROOT_PATH,
);
set_include_path(implode(PATH_SEPARATOR,$libraryPath));
