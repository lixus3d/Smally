<?php

namespace Smally ;

class HtmlUtil {

	/**
	 * Convert an array of key => values to xml/html attributes
	 * @param  array $attributes Array of key => values
	 * @param  string $enclosure  enclose attribute with this char
	 * @param  string $equal      equal sign char
	 * @param  string $sep        separator between attributes
	 * @return string
	 */
	static public function toAttributes(array $attributes,$enclosure='"',$equal='=',$sep=' '){
		$str = "";
		if (is_array($attributes)) {
			$str .= $sep;
			foreach($attributes as $key => $value) {
				if(is_array($value)) $value = implode(' ',$value); // case of "class" is practical under array type
				switch (true) {
					case (strlen($value) > 0) : // a value must be defined , except for special $key
					case $key == 'action' : // action key has valid empty value
						$str .= $key . $equal . $enclosure . $value . $enclosure .$sep;
						break;
				}
			}
		}
		return rtrim($str);
	}

}