<?php

namespace Smally;

abstract class AbstractUrlRewriting {

	protected $_urlRewriting = array();

	/**
	 * Add a rewrite rule
	 * @param string $rule Can be a equal string or a regex if leaded by a '#' char
	 * @param mixed $options Can be a string or an array for regex with the matches key
	 */
	public function addRule($rule,$options){
		if($rule && $options){
			$this->_urlRewriting[] = array('rule'=>$rule,'options'=>$options);
		}
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