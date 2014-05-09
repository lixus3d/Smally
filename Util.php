<?php

namespace Smally ;

class Util {

	/**
	 * Convert an array of key => values to xml/html attributes
	 * @param  array $attributes Array of key => values
	 * @param  string $enclosure  enclose attribute with this char
	 * @param  string $equal      equal sign char
	 * @param  string $sep        separator between attributes
	 * @return string
	 */
	static public function toAttributes(array $attributes,$enclosure='"',$equal='=',$sep=' '){
		$str = '';
		if (is_array($attributes)) {
			foreach($attributes as $key => $value) {
				if(is_array($value)) $value = implode(' ',$value); // case of "class" is practical under array type
				switch (true) {
					case (strlen($value) > 0) : // a value must be defined , except for special $key
					case $key == 'action' : // action key has valid empty value
						$str .= $sep . $key . $equal . $enclosure . htmlspecialchars($value,ENT_COMPAT,'UTF-8',false) . $enclosure;
						break;
				}
			}
		}
		return $str;
	}

	/**
	 * Return the hash for a given password
	 * @param  string $password the string to hash
	 * @param  string $salt     An optionnal hash, set to empty string for no salt
	 * @return string The hash of the given $password
	 */
	static public function passHash($password,$salt=null){
		if(is_null($salt)) $salt = (string)\Smally\Application::getInstance()->getConfig()->smally->salt?:'sel';
		return md5($password.$salt);
	}

	/**
	 * Return an url or id readable string of a given text
	 * @param  string $string    A string you want to slugify
	 * @param  string $separator The separator of each part, usually -
	 * @return string The slugified string
	 */
	static public function slugify($string,$separator='-'){
		$value = trim($string);
		// convert accent
		$value = iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$value);
		// lower case the string
		$value = strtolower($value);
		// convert space, comma, tabulation, etc to '-'
		$value = preg_replace('#[\s,.\\\\/\n]+#',$separator,$value);
		$value = preg_replace('#[^a-z0-9'.$separator.']#','',$value);
		$value = preg_replace('#'.$separator.'{2,}#',$separator,$value);
		return trim($value,'-');
	}

	/**
	 * Special mkdir method that will create each needed folder until the $path is really created
	 * @param  string $path The path you want to create
	 * @return boolean Return of is_dir($path) of the given $path
	 */
	static public function fullmkdir($path){
		$path = str_replace(array('/','\\'), DIRECTORY_SEPARATOR, $path);
		$pathParts = explode(DIRECTORY_SEPARATOR,$path);
		$path = '';
		foreach($pathParts as $k => $part){
			if(!$part) continue;
			// if($part=='.') continue;
			// if($part=='..'){
			// 	$path = substr($path, 0, strrpos($path,DIRECTORY_SEPARATOR,-2)).DIRECTORY_SEPARATOR;
			// 	continue;
			// }
			$path .= $part;
			if(!@is_dir($path)){
				@mkdir($path);
				@chmod($path,0777);
			}
			$path .= DIRECTORY_SEPARATOR;
		}
		return is_dir($path);
	}

}