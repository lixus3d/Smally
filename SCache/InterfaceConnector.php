<?php

namespace Smally\SCache;

interface InterfaceConnector {

	public function setKey($key,$value,$ttl);
	public function getKey($key);
	public function hasKey($key);
	public function deleteKey($key);

}