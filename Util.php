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
				if(is_array($value)){
					switch ($key) {
						case 'style':
							$valSeparator = ';';
							break;
						default:
							$valSeparator = ' ';
							break;
					}
					$value = implode($valSeparator,$value); // case of "class" is practical under array type
				}
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
		// // old school but reliable solution for common accents
		// $a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ', 'Ά', 'ά', 'Έ', 'έ', 'Ό', 'ό', 'Ώ', 'ώ', 'Ί', 'ί', 'ϊ', 'ΐ', 'Ύ', 'ύ', 'ϋ', 'ΰ', 'Ή', 'ή');
		// $b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o', 'Α', 'α', 'Ε', 'ε', 'Ο', 'ο', 'Ω', 'ω', 'Ι', 'ι', 'ι', 'ι', 'Υ', 'υ', 'υ', 'υ', 'Η', 'η');
		// $value = str_replace($a, $b, $value);
		$value = self::convertAccent($value);
		// $value = iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$value);
		// lower case the string
		$value = strtolower($value);
		// convert space, comma, tabulation, etc to '-'
		$value = preg_replace('#[\s,.\\\\/\n]+#',$separator,$value);
		$value = preg_replace('#[^a-z0-9'.$separator.']#','',$value);
		$value = preg_replace('#'.$separator.'{2,}#',$separator,$value);
		return trim($value,$separator);
	}

	static public function convertAccent($string){
		$a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ', 'Ά', 'ά', 'Έ', 'έ', 'Ό', 'ό', 'Ώ', 'ώ', 'Ί', 'ί', 'ϊ', 'ΐ', 'Ύ', 'ύ', 'ϋ', 'ΰ', 'Ή', 'ή');
		$b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o', 'Α', 'α', 'Ε', 'ε', 'Ο', 'ο', 'Ω', 'ω', 'Ι', 'ι', 'ι', 'ι', 'Υ', 'υ', 'υ', 'υ', 'Η', 'η');
		return str_replace($a, $b, $string);
	}

	/**
	 * Multibyte string split (NOT TESTED)
	 * @param  string $string A string to split in 1 char array
	 * @param  string $enc    The char encoding of the string
	 * @return array
	 */
	static public function mb_str_split($string, $enc = "UTF-8") {
		$strlen = mb_strlen($string);
		for($i=0;$i<$strlen;$i++){
			$array[] = mb_substr($string,$i,1,$enc);
		}
		return $array;
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
	static public function getRevelantText($text, $search, $boldify=true, $partify=true, $before=50, $after=40, $length=50){

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
			$length = $length + $before + $after;
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

		if($boldify){
			foreach($searchTerms as $term){
				$text = str_replace($term,'<strong>'.$term.'</strong>',$text);
			}
		}

		return $text;
	}

	/**
	 * Recursive rmdir that will also delete sub files and directories
	 * @param string $dir The directorty to recursive delete
	 * @return boolean
	 */
	static public function rrmdir($dir) {
		$structure = glob(rtrim($dir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'*');
		if (is_array($structure)) {
			foreach($structure as $file) {
				if (is_dir($file)) self::rrmdir($file);
				elseif (is_file($file)) unlink($file);
			}
		}
		return rmdir($dir);
	}

}