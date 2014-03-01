<?php

namespace Smally\VOMeta ;

class AbstractVOMeta {

	public function c($constname){
		return constant('static::'.$constname);
	}

}