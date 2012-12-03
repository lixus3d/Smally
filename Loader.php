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

		if($parts = explode('_',$className)){

			$classItself = array_pop($parts);
			$classItself = str_replace('\\','/',$classItself); // Change path issue
			$classItself = preg_replace('#^(\\\\)?Controller#','$1controller',$classItself);

			// Paths where we search
			$possibleBasePath = self::getBasePath();

			foreach($possibleBasePath as $base){

				$path = '';
				foreach($parts as $key => $part){
					$path .= $part;
					if(!is_dir($base.$path)){
						continue(2); // if folder is not good, try another basePath
					}
					$path .= '/';
				}
				if(file_exists($base.$path.$classItself.'.php')){
					require_once($base.$path.$classItself.'.php');
					return true;
				}
			}

		}else throw new Exception('Invalid classname');
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
