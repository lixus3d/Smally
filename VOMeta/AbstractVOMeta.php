<?php

namespace Smally\VOMeta ;

class AbstractVOMeta {

	/**
	 * Add the possibility to call constant by string representation
	 * Maybe better solution to solve the ->getVOMeta()::CONSTANT not working
	 * @param  string $constname The constant name in a string
	 * @return mixed The constant value 
	 */
	public function c($constname){
		return constant('static::'.$constname);
	}

}