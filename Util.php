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
			if(!$part&&$k!==0) continue;
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

	/**
	 * Extract parts from a $text that contains $search terms
	 * @param  string  $text    The text you want to extract parts from
	 * @param  string  $search  The search you want to extract
	 * @param  boolean $boldify Default will bold search terms
	 * @param  boolean $partify Default will extract multiple parts
	 * @param  integer $before  Number of chars to extract before the search term
	 * @param  integer $after   Number of chars to extract eafter the search term
	 * @return string
	 */
	static public function getRevelantText($text, $search, $boldify=true, $partify=true, $before=50, $after=40){

		$splitChars = str_split(" \n\t,.:;");

		$originalText = $text;
		$textLength = strlen($text);

		// we split the search
		$searchTerms = explode(' ', $search);
		$searchTerms[] = $search;
		foreach($searchTerms as $term){
			$searchTerms[] = ucfirst($term);
			$searchTerms[] = strtoupper($term);
			$searchTerms[] = strtolower($term);
		}

		$parts = array();

		if($partify){

			// We find all term positions
			$termPlaceList = array();
			foreach($searchTerms as $term){
				$offset = 0;
				while( ($pos = strpos($text,$term,$offset)) !== false ){
					$termPlaceList[$pos] = $term;
					$offset = $pos+1;
				}
			}
			ksort($termPlaceList);


			// We extract parts of text
			foreach($termPlaceList as $position => $term){
				$begin = $position - $before;
				if( $begin <= 0 ){
					$begin = 0;
					$prefix = '';
				}else{
					while( true ){
						if( in_array(mb_substr($text,$begin,1,'UTF-8'),$splitChars) ){
							$begin++;
							break;
						}else{
							$begin--;
						}
						if( $begin == 0 ) break;
					}
					$prefix = '... ';
				}

				$end = $position + strlen($term) + $after;
				if( $end > ($textLength-1) ){
					$end = $textLength - 1;
					$suffix = '';
				}else{
					while( true ){
						if( in_array(mb_substr($text,$end,1,'UTF-8'),$splitChars) ){
							break;
						}else{
							$end++;
						}
						if( $end == ($textLength - 1) ) break;
					}
					$suffix = ' ...';
				}
				// print_r(array($begin,$end));
				$parts[] = $prefix . mb_substr($text, $begin, $end - $begin, 'UTF-8') . $suffix;
			}
		}

		if($parts){
			$text = trim(implode($parts,''));
			$text = str_replace('......',' ... ',$text);
		}else{
			$length = 50 + $before + $after;
			while( true ){
				if( in_array(mb_substr($text,$length,1,'UTF-8'),$splitChars) ){
					// $length--;
					break;
				}else{
					$length++;
				}
				if( $length >= ($textLength) ) break;
			}
			if( $length > $textLength){
				$text = $text;
			}else{
				$text = mb_substr($originalText, 0, $length, 'UTF-8').' ...';
			}
		}


		foreach($searchTerms as $term){
			$text = str_replace($term,'<strong>'.$term.'</strong>',$text);
		}

		return $text;
	}

}