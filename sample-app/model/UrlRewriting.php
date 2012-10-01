<?php

class UrlRewriting extends \Smally\AbstractUrlRewriting {

	public function init(){
		// Basic rule
		$this->addRule('albums.html', 'Index/albums');
		// REGEX rule
		$this->addRule('#^regex-rule-([0-9]+)\.html$#', array('path'=>'Index/regex','matches'=>array('match','numeric')));
	}

}