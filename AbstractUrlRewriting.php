<?php

namespace Smally;

abstract class AbstractUrlRewriting {

	protected $_urlRewriting = array();
	protected $_controllerRewriting = array();
	protected $_redirectRules = array();

	protected $_replaceParams = null;

	public function __construct(){
		$this->init();
	}

	abstract public function init();

	/**
	 * Add a rewrite rule
	 * @param string $rule Can be a equal string or a regex if leaded by a '#' char
	 * @param mixed $options Can be a string or an array for regex with the matches key
	 * @return \Smally\AbstractUrlRewriting
	 */
	public function addRule($rule,$options){
		if($options){
			$array = array('rule'=>$rule,'options'=>$options);
			$this->_urlRewriting[] = &$array ;

			if(is_array($options)) $destination = $options['path'];
			else $destination = $options;

			if($destination){ // If the destination (options) is a string, the controller has a particular url for being accessed
				$this->_controllerRewriting[strtolower($destination)] = &$array;
				if(strpos($destination,'Index\\')===0){ // if it's a default Index controller action, must create two rule
					$this->_controllerRewriting[substr($destination,6)] = &$array;
				}
			}
		}

		return $this;
	}

	/**
	 * Add a redirect rules to map a ancient site url to a the new one
	 * @param string $oldUrl Url on the ancient site
	 * @param string $newUrl Url on the new (current) site
	 * @return \Smally\AbstractUrlRewriting
	 */
	public function addRedirectRule($oldUrl, $newUrl, $httpStatus=301){
		if($oldUrl && $oldUrl != $newUrl){
			$this->_redirectRules[$oldUrl] = array('url'=>$newUrl,'httpStatus'=>$httpStatus);
		}
		return $this;
	}

	/**
	 * Return the url rewriting of a given controller path
	 * @param  string $controllerPath the controller path to convert
	 * @param  array  $params         if the url rewrite is regex based, params to provide to make it reverse
	 * @return string null in case of no matching rule
	 */
	public function getControllerRewriting($controllerPath,$params=array()){
		$controllerPathLowered = strtolower($controllerPath);
		$getPart = $params?'?'.http_build_query($params):'';
		if(isset($this->_controllerRewriting[$controllerPathLowered])){
			$rule = $this->_controllerRewriting[$controllerPathLowered];
			$test = $rule['rule'];
			if(strpos($test,'#')===0){
				if(isset($rule['options']['reverse'])&&$params){
					$this->_replaceParams = $params;
					return preg_replace_callback('#%([a-z]+)#', array($this,'paramToUrl'), $rule['options']['reverse'] );
				}
			}else{
				return $test.$getPart;
			}
		}
		return str_replace('\\','/',$controllerPath).$getPart;
	}

	/**
	 * Replace in $matches the param key with the value for url
	 * @example replace '%name' with 'emmanuel-gauthier' from the $this->_replaceParams
	 * @param  array $matches Array of matches from preg_replace_callback
	 * @return string
	 */
	public function paramToUrl($matches){
		$param = $matches[1];
		$value = isset($this->_replaceParams[$param])?$this->_replaceParams[$param]:'';
		// convert accent
		$value = iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$value);
		// lower case the string
		$value = strtolower($value);
		// convert space, comma, tabulation, etc to '-'
		$value = preg_replace('#[\s,.\\\\/\n]+#','-',$value);
		$value = preg_replace('#-{2,}#','-',$value);
		return preg_replace('#[^a-z0-9-]#','',$value);
	}

	/**
	 * Return the redirect array if a $url is found in redirect rules
	 * @param string $url The url you want to test on redirect rule s
	 * @return mixed
	 */
	public function hasRedirectRule($url){
		if(array_key_exists($url, $this->_redirectRules)){
			return $this->_redirectRules[$url];
		}
		return false;
	}


	/**
	 * Return the url rewriting if specific or false if no specific rule found
	 * @param  string  $controllerPath The controllerpath to test
	 * @param  array   $params Array if $key => $value to place in the url
	 * @return mixed
	 */
	public function hasControllerRewriting($controllerPath){
		$controllerPathLowered = strtolower($controllerPath);
		return isset($this->_controllerRewriting[$controllerPathLowered]);
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