<?php

namespace Smally;

class Config {

	/**
	 * Config construct, you can give a $path to a config file (php array format)
	 * @param mixed $path Path to a config file in php array format
	 */
	public function __construct($path=null){
		if($path && is_file($path)){
			require($path);
			if(isset($config) && is_array($config)) $this->setConfig($config);
		}
	}

	/**
	 * Define the config key => value in a recursive way
	 * @param array $config An array of config value
	 */
	public function setConfig(array $config){
		foreach($config as $key => $value){
			if(is_array($value)){
				$this->{$key} = new Config();
				$this->{$key}->setConfig($value);
			}else{
				$this->{$key} = $value;
			}
		}
	}

	/**
	 * When a undefined key is __get return an empty Config object for compatibilty
	 * @param  string $key
	 * @return \Smally\Config Empty config object
	 */
	public function __get($key){
		return new self();
	}

	/**
	 * Return a empty string when converted to string
	 * @return string
	 */
	public function __toString(){
		return '';
	}
}