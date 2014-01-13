<?php

namespace Smally\Filter;

class UrlRewrite extends AbstractRule {

	/**
	 * Filter the $value to be valid for a urlrewrite rule
	 * @param  mixed $value
	 * @return boolean
	 */
	public function x($value){
		if(is_array($value)) $value = array_shift($value);
		if(is_null($value)) return null;

		if($value!=''){
			// convert accent
			$value = iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$value);
			// lower case the string
			$value = strtolower($value);
			// convert space, comma, tabulation, etc to '-'
			$value = preg_replace('#[\s,.\n]+#','-',$value);
			// keep only alphanumeric, - , \ and #
			$value = preg_replace('#[^a-z0-9/\#-]#','',$value);
			// trim trailing -, #, /
			$value = trim($value,'#-/');
		}

		return $value;
	}

}