<?php

namespace Smally;

class Response {

	protected $_application = null;

	protected $_headers = array();
	protected $_content = null;

	/**
	 * Construct the Response object
	 * @param \Smally\Application $application reverse reference to the application
	 */
	public function __construct(\Smally\Application $application){
		$this->setApplication($application);
	}

	/**
	 * Set the application reverse reference
	 * @param \Smally\Application $application Current application linked to this object
	 * @return \Smally\Response
	 */
	public function setApplication(\Smally\Application $application){
		$this->_application = $application;
		return $this;
	}

	/**
	 * Return the application reverse referenced
	 * @return \Smally\Application
	 */
	public function getApplication(){
		return $this->_application;
	}

	/**
	 * Define group of headers for the response
	 * @param array $headers Multiple header line
	 * @return \Smally\Response
	 */
	public function setHeaders($headers){
		if(is_array($headers) && $headers){
			foreach($headers as $header){
				$this->setHeader($header);
			}
		}
		return $this;
	}

	/**
	 * Define a header for the response
	 * @param string $header A header entry
	 */
	public function setHeader($header){
		$this->_headers[$header] = $header;
		return $this;
	}

	/**
	 * Define the response content
	 * @param string $content the content
	 */
	public function setContent($content){
		$this->_content = $content;
		return $this;
	}

	/**
	 * Execute the response logic : send headers, then send content
	 * @return \Smally\Response
	 */
	public function x(){
		foreach($this->_headers as $header){
			header($header);
		}
		echo $this->_content;
		return $this;
	}

}