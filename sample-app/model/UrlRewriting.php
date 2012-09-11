<?php

class UrlRewriting extends \Smally\AbstractUrlRewriting {

	public function __construct(){
		// Basic rule
		$this->addRule('albums.html', 'Index/albums');
		// REGEX rule
		$this->addRule('#^regex-rule-([0-9]+)\.html$#', array('path'=>'Index/regex','matches'=>array('match','numeric')));
	}

}