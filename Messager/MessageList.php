<?php

namespace Smally\Messager;

/**
 * Generic Logger class for all your logs
 */

class MessageList {
	public $messages = array();

	public function addMessage(Message $message){
		$this->messages[] = $message;
	}

	public function getMessages(){
		return $this->messages;
	}

	public function reset(){
		$this->messages = array();
	}
}