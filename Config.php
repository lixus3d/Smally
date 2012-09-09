<?php

namespace Smally;

class Config {

	public function __construct($path=null){
		if($path && is_file($path)){
			require($path);
			if(isset($config) && is_array($config)) $this->setConfig($config);
		}
	}

	public function setConfig($config){
		foreach($config as $key => $value){
			if(is_array($value)){
				$this->{$key} = new Config();
				$this->{$key}->setConfig($value);
			}else{
				$this->{$key} = $value;
			}
		}
	}

	public function __get($key){
		return new self();
	}

	public function __toString(){
		return '';
	}
}