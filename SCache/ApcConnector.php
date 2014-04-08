<?php

namespace Smally\SCache;

class ApcConnector implements InterfaceConnector {

	public function setKey($key,$value,$ttl=36000){
		return apc_store($key,$value,$ttl);
	}

	public function getKey($key){
		if( $this->hasKey($key) ){
			return apc_fetch($key);
		}
		return null;
	}

	public function hasKey($key){
		return apc_exists($key);
	}

	public function deleteKey($key){
		if( $this->hasKey($key) ){
			return apc_delete($key);
		}
		return null;
	}

	/**
	 * Delete keys by a regex matching key names
	 * @param  string $keyRegex A regex to match againt
	 * @return boolean
	 */
	public function deleteKeys($keyRegex){
		$toDelete = new \APCIterator('user', $keyRegex, APC_ITER_VALUE);
		return apc_delete($toDelete);
	}

}