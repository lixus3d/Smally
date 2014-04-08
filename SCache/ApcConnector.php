<?php

namespace Smally\SCache;

class ApcConnector implements InterfaceConnector {

	public function isActive(){
		return function_exists('apc_exists');
	}

	public function setKey($key,$value,$ttl=36000){
		if($this->isActive()){
			return apc_store($key,$value,$ttl);
		}
		return false;
	}

	public function getKey($key){
		if($this->isActive()){
			if( $this->hasKey($key) ){
				return apc_fetch($key);
			}
			return null;
		}
		return false;
	}

	public function hasKey($key){
		if($this->isActive()){
			return apc_exists($key);
		}
		return false;
	}

	public function deleteKey($key){
		if($this->isActive()){
			if( $this->hasKey($key) ){
				return apc_delete($key);
			}
			return null;
		}
		return false;
	}

	public function deleteKeys($keyRegex){
		if($this->isActive()){
			$toDelete = new \APCIterator('user', $keyRegex, APC_ITER_VALUE);
			return apc_delete($toDelete);
		}
	}

}