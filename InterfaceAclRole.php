<?php

namespace Smally;

interface InterfaceAclRole {

	static public function getInstance();
	public function hasRight($right);

}