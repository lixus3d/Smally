<?php

namespace Smally ;

class HtmlUtil {

	static public function toAttributes($attributes,$enclosure='"',$equal='=',$sep=' '){
		$str = "";
		if (is_array($attributes)) {
			$str .= $sep;
			foreach($attributes as $key => $value) {
				if(is_array($value)) $value = implode(' ',$value); // case of "class" is practical under array type
				switch (true) {
					case (strlen($value) > 0) :
					case $key == 'action' :
						$str .= $key . $equal . $enclosure . $value . $enclosure .$sep;
						break;
				}
			}
		}

		return rtrim($str);
	}

}