<?php

namespace Smally;

class ControllerAcl {

	static protected $_singleton = null;

	protected $_rules = array();
	protected $_defaultRedirect = 'Index\\index';

	/**
	 * Construct a new ControllerAcl object and load rules from file
	 */
	public function __construct(){
		if(!self::$_singleton instanceof self){
			$this->setInstance();
		}

		$this->loadFile();
	}

	/**
	 * Set the singleton instance of ControllerAcl
	 * @return \ControllerAcl
	 */
	public function setInstance(){
		return self::$_singleton = $this;
	}

	/**
	 * Return the singleton
	 * @return \ControllerAcl
	 */
	static public function getInstance(){
		if(!self::$_singleton instanceof ControllerAcl){
			new self();
		}
		return self::$_singleton;
	}

	/**
	 * Load acl rules from config file
	 * @return \Smally\ControllerAcl
	 */
	public function loadFile(){
		$path = (string) \Smally\Application::getInstance()->getConfig()->controller->aclFilePath ?: CONFIG_PATH.'acl.php';
		if(file_exists($path)){
			require($path);
			if(isset($rules)) $this->setRules($rules);
			if(isset($redirect)) $this->setDefaultRedirect($redirect);
		}
	}

	/**
	 * Set the controller acl rules for each controller
	 * @param array $rules Controller rules, setRule style array
	 */
	public function setRules($rules){
		$this->_rules = $rules;
		return $this;
	}

	/**
	 * Set a rule for a particular controller
	 * @param string $controllerPath The controller path , can be with action for specific acl by action or without action for global acl to the entire controller actions
	 * @param array $rules         The array of rules , acl style array
	 */
	public function setRule($controllerPath,$rules){
		$this->_rules[$controllerPath] = $rules;
		return $this;
	}

	/**
	 * Set the default redirect path
	 * @param string $defaultRedirect The default controller path on missing redirect
	 */
	public function setDefaultRedirect($defaultRedirect){
		$this->_defaultRedirect = $defaultRedirect;
	}

	/**
	 * Return a new Acl object
	 * @return \Smally\Acl
	 */
	public function getAcl(){
		return \Smally\Application::getInstance()->getAcl();
	}

	/**
	 * Check if the given controller path (with action) is acl valid. If no rules found, consider valid, so take care of rules definition
	 * @param  string $controllerPath The controller path with action part (But no Controller namespace)
	 * @return boolean Only return true, if not valid , automatically redirected so no return produced. Be carrefull that Index/index is public of redirect to the public login page if not
	 */
	public function check($controllerPath,$redirect=true){

		$originalControllerPath = $controllerPath;

		if($this->_rules){
			// find a rule recursively thru controller hierarchy
			while(!isset($rule)){
				if(isset($this->_rules[$controllerPath])) {
					$rule = $this->_rules[$controllerPath];
				}else{
					if( strpos($controllerPath,'\\') !==false ){
						$controllerPath = substr($controllerPath,0,strrpos($controllerPath,'\\'));
					}else break; // we break if we didn't find any \\ char because we have reach the top
				}
			}

			if(!isset($rule)){
				// We try regex rule if nothing found
				foreach($this->_rules as $key => $rRule){
					if( strpos($key, '#') === 0){ // regex rule
						if( preg_match($key,$originalControllerPath) ){
							$rule = $rRule;
						}
					}
				}
			}

			if( isset($rule) && $rule ){
				$acl = $this->getAcl();
				return $acl
					->setAllowArray($rule['allow'])
					->check( $redirect ? (isset($rule['redirect'])?$rule['redirect']:$this->_defaultRedirect) : null );
			}

		}

		return true;
	}

}