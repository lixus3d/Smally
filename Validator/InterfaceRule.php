<?php

namespace Smally\Validator;

interface InterfaceRule {

	public function x($valueToTest);
	public function getError();

}