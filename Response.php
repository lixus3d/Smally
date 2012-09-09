<?php

namespace Smally;

class Response {

	protected $_application = null;

	protected $_headers = array();
	protected $_content = null;


	public function __construct(\Smally\Application $application){
		$this->setApplication($application);
	}

	public function setApplication(\Smally\Application $application){
		$this->_application = $application;
		return $this;
	}

	public function getApplication(){
		return $this->_application;
	}

	public function setHeaders($headers){
		if(is_array($headers) && $headers){
			foreach($headers as $header){
				$this->setHeader($header);
			}
		}
		return $this;
	}

	public function setHeader($header){
		$this->_headers[$header] = $header;
		return $this;
	}

	public function setContent($content){
		$this->_content = $content;
		return $this;
	}

	public function x(){
		foreach($this->_headers as $header){
			header($header);
		}
		echo $this->_content;
		return $this;
	}

}