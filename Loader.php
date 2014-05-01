<?php

namespace Smally;

/**
 * My autoloader compatible with namespace and psr-0 logic
 */
class Loader {

	static $basePath = null;

	/**
	 * load a specific class by finding the correct path
	 * @param string $className
	 * @throws Exception
	 */
	static public function load($className){

		if(DIRECTORY_SEPARATOR !== '\\'){
			$path = str_replace('\\',DIRECTORY_SEPARATOR,$className).'.php';
		}else $path = $className.'.php';

		if( ($absPath = stream_resolve_include_path($path)) !== false){
			include_once($absPath);
		}

	}

	/**
	 * Get the base path where we have to search for class
	 * @return string;
	 */
	static public function getBasePath(){
		if(is_null(self::$basePath)){
			self::$basePath = explode(PATH_SEPARATOR,get_include_path());
		}
		return self::$basePath;
	}
}

// We register this autoloader
spl_autoload_register(__NAMESPACE__.'\Loader::load');
