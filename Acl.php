<?php

namespace Smally;

class Acl {

	/* Logic of the $_allow property

		$_allow = array(
			'User' => array(
				'news',
				'blog'
			),
			'Admin' => array(
				'news',
				'blog',
				'users'
			),
		);

	 */
	protected $_allow = array();


	/**
	 * Define directly the _allow array with manu rules
	 * @param array $array Array of model => rights
	 */
	public function setAllowArray($array){
		if(is_array($array)){
			$this->_allow = $array;
		}
		return $this;
	}

	/**
	 * Add a rule to the Acl checker
	 * @param  string $model the model name to check for $rules
	 * @param  mixed $rules A right or array of rights
	 * @return \Smally\Acl
	 */
	public function allow($model,$rules=null){

		$implements = class_implements($model);

		if(!in_array('Smally\InterfaceAclRole',$implements)){
			throw new Exception('Model '.$model.' is not an InterfaceAclRole interface !');
		}

		if(!isset($this->_allow[$model])) $this->_allow[$model] = array();
		if(!is_array($rules)) $rules = array($rules);

		foreach($rules as $rule){
			if(!in_array($rule,$this->_allow[$model])){
				$this->_allow[$model][]= $rule;
			}
		}
		return $this;
	}

	/**
	 * Check if any of allow rules is satisfy
	 * @param  string $redirect Optionnal redirect destination if acl is not satisfy
	 * @return boolean
	 */
	public function check($redirect=null){
		$state = false;
		foreach($this->_allow as $modelName => $rights){
			if(method_exists($modelName, 'getInstance')){
				if($instance = $modelName::getInstance()){
					foreach($rights as $r){
						switch(true){
							case is_null($r):// If we have a null rule, just a valid getInstance satisfy the rule
							case $instance->hasRight($r):
								$state = true;
						}
						if($state==true) break;
					}
				}
			}else throw new Exception('Model '.$modelName.' has no getInstance method ! Acl check fail !');
		}
		if(!is_null($redirect)&&!$state){
			Application::getInstance()->getRooter()->redirect($redirect);
		}
		return $state;
	}

}