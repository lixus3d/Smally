<?php

require('./boot.php');

try{
	// Require the Smally autoloader
	require_once(LIBRARY_PATH.'Smally/Loader.php');
	// Create a new Smally Application
	Smally\Application::getInstance()
		// Set the application environnement
		->setEnvironnement(getenv('PROJECT_ENVIRONNEMENT')?:Smally\Application::ENV_DEVELOPMENT)
		// Set a config
		->setConfig(new Smally\Config(ROOT_PATH.'config.php'))
		// Execute the application logic
		->x()
		;
}catch( Exception $e ){
	//@ob_end_clean(); // if you want to only have the error message
	echo $e->getMessage();
}

?>