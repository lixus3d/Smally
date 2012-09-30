<?php

namespace Smally;

class Rightable {

	protected $_rights = null;

	/**
	 * Put the specific right to the model
	 * @param int $right
	 */
	public function setRight($right){
		$this->_rights |= $right;
		return $this;
	}

	/**
	 * Get rights of the model
	 * @param boolean $array
	 * @return mixed
	 */
	public function getRight($array=false){
		if($array){
			$return = array();
			for($i=1;$i<=63;$i++){
				$pow = pow(2,$i);
				if( $pow & $this->_rights ) $return[] = $pow;
			}
			return $return;
		}
		return $this->_rights;
	}

	/**
	 * Is their this right in the model
	 * @param int $right
	 */
	public function hasRight($right){
		return ($this->_rights & $right)? true : false ;
	}
}