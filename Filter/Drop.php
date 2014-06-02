<?php

namespace Smally\Filter;

class Drop extends AbstractRule {

	/**
	 * Drop the value
	 * @param  mixed $value
	 * @return boolean
	 */
	public function x($value){
		return null;
	}

}