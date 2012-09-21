<?php

namespace Smally;

abstract class AbstractUrlRewriting {

	protected $_urlRewriting = array();
	protected $_controllerRewriting = array();

	public function __construct(){
		$this->addRule('','Index/index'); // The most default rewrite rule to avoid SEO duplicate content
		$this->init();
	}

	abstract public function init();

	/**
	 * Add a rewrite rule
	 * @param string $rule Can be a equal string or a regex if leaded by a '#' char
	 * @param mixed $options Can be a string or an array for regex with the matches key
	 */
	public function addRule($rule,$options){
		if($options){
			$array = array('rule'=>$rule,'options'=>$options);
			$this->_urlRewriting[] = &$array ;

			if(is_array($options)) $destination = $options['path'];
			else $destination = $options;

			if($destination){ // If the destination (options) is a string, the controller has a particular url for being accessed
				$this->_controllerRewriting[strtolower($destination)] = &$array;
				if(strpos($destination,'Index/')===0){ // if it's a default Index controller action, must create to rule
					$this->_controllerRewriting[substr($destination,6)] = &$array;
				}
			}
		}
	}

	/**
	 * Return the url rewriting of a given controller path
	 * @param  string $controllerPath the controller path to convert
	 * @param  array  $params         if the url rewrite is regex based, params to provide to make it reverse
	 * @return string null in case of no matching rule
	 */
	public function getControllerRewriting($controllerPath,$params=array()){
		$controllerPath = strtolower($controllerPath);
		if(isset($this->_controllerRewriting[$controllerPath])){
			$rule = $this->_controllerRewriting[$controllerPath];
			$test = $rule['rule'];
			if(strpos($test,'#')===0){
				if(isset($rule['options']['reverse'])){
					//return sprintf($rule['options']['reverse'],$params);

					return call_user_func_array('sprintf',array_merge(array($rule['options']['reverse']),$params));
				}
			}else{
				return $test;
			}
		}
		return null;
	}

	/**
	 * Evaluate a string with the rewrite rules and return the result (null if it can't find a matching rule)
	 * @param  string $url String to evaluate
	 * @return mixed
	 */
	public function getRewrite($url=''){
		$return = null;

		foreach($this->_urlRewriting as $rule){

			$test = $rule['rule'];
			$destination = $rule['options'];

			// regex mode
			if(strpos($test,'#')===0){
				if(preg_match($test,$url,$matches)){
					// if we have an array destination, then we have to set the matches
					if(is_array($destination)){
						if(isset($destination['matches'])){
							if( count($destination['matches']) != count($matches)){
								if(count($destination['matches']) > count($matches)){
									$destination['matches'] = array_slice($destination['matches'],0,count($matches));
								}else{
									$matches = array_slice($matches,0,count($destination['matches']));
								}
							}
							$destination['matches'] = array_combine($destination['matches'], $matches);
						}else $destination['matches'] = $matches;
					}
					$return = $destination;
					break;
				}
				// equal mode
			}else{
				if($url==$test){
					$return = $destination;
					break;
				}
			}
		}
		return $return;
	}


}