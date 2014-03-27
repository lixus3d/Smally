<?php

namespace Smally;

class Messager {

	const LVL_SUCCESS	= 'success';
	const LVL_INFO 		= 'info';
	const LVL_WARNING 	= 'warning';
	const LVL_ERROR 	= 'error';

	static protected $_singleton 	= null;

	protected $_session = null;
	protected $_application = null;

	protected $_messagerList = null;

	/**
	 * Construct a new Messager object and start the session
	 */
	public function __construct(){
		if(!self::$_singleton instanceof self){
			$this->setInstance();
		}

		if( $this->getSession()->smallyMessager instanceof Messager\MessageList ){
			$this->_messagerList = $this->getSession()->smallyMessager;
		}else{
			$this->_messagerList = new Messager\MessageList();
		}
	}

	/**
	 * Set the singleton instance of Messager
	 * @return \Smally\Messager
	 */
	public function setInstance(){
		return self::$_singleton = $this;
	}

	/**
	 * Return the singleton
	 * @return \Smally\Messager
	 */
	static public function getInstance(){
		if(!self::$_singleton instanceof Messager){
			new self();
		}
		return self::$_singleton;
	}

	public function getSession(){
		if(is_null($this->_session)){
			$this->_session = Session::getInstance();
		}
		return $this->_session;
	}

	public function getApplication(){
		if(is_null($this->_application)){
			$this->_application = \Smally\Application::getInstance();
		}
		return $this->_application;
	}

	/**
	 * Push message to session
	 * @param string $content Html you want to show on the next showed page
	 * @param int $level 	The level/type of the message you want to show
	 */
	public function addMessage($content,$level=self::LVL_INFO,$title=null){
		$this->getMessagerList()->addMessage( new Messager\Message($content,$level,$title) );
		return $this;
	}

	public function success($content,$title=null){
		return $this->addMessage($content,self::LVL_SUCCESS,$title);
	}
	public function info($content,$title=null){
		return $this->addMessage($content,self::LVL_INFO,$title);
	}
	public function warning($content,$title=null){
		return $this->addMessage($content,self::LVL_WARNING,$title);
	}
	public function error($content,$title=null){
		return $this->addMessage($content,self::LVL_ERROR,$title);
	}

	public function getMessagerList(){
		return $this->_messagerList;
	}

	public function resetMessagerList(){
		$this->getMessagerList()->reset();
	}

	public function x(){

		if( $messages = $this->getMessagerList()->getMessages() ){ // we have message to show so we load the necessary js
			$this->getApplication()
						->setJs('js/vendors/toastr/toastr.min.js')
						->setCss('js/vendors/toastr/toastr.min.css')
						->setJs('js/smally/Smally.js')
						->setJs('js/smally/Ajax.js')
						->setJs('js/smally/Messager.js') // will call Messager Rpc on document load
						;
		}

		$this->getSession()->smallyMessager = $this->getMessagerList();

	}

}

