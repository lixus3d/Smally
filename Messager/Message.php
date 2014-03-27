<?php

namespace Smally\Messager;

class Message {

	public $content = null;
	public $title = null;
	public $level = null;

	public function __construct($content=null,$level=\Smally\Messager::LVL_INFO,$title=null){
		$this->content = $content;
		$this->level = $level;
		$this->title = $title;
	}
}