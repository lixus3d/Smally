<?php

namespace Smally\Form\Element;

class Submodel extends AbstractElement{

	protected $_type = 'text';
	protected $_decorator = 'submodel';

	protected $_voName = null;
	protected $_voFields = array();

	protected $_checked = array();

	/**
	 * Define the vo name of the sub item
	 * @param string $voName The vo name of the sub item you want to add
	 * @return  \Smally\Form\Element\TagList
	 */
	public function setVoName($voName){
		$this->_voName = $voName;
		return $this;
	}

	public function setVoFields($fields){
		$this->_voFields = $fields;
	}

	/**
	 * Return the selected/checked options of the Field
	 * @return array
	 */
	public function getChecked(){
		return $this->_checked;
	}

	/**
	 * Return the vo name of the sub item
	 * @return string
	 */
	public function getVoName(){
		return $this->_voName;
	}

	public function getVoFields(){
		return $this->_voFields;
	}

	public function getValue(){
		$return = array();
		if($this->getChecked() && $app = \Smally\Application::getInstance()){
			$dao = $app->getFactory()->getDao($this->getVoName());
			$criteria = $dao->getCriteria();
			$criteria ->setFilter(array($dao->getPrimaryKey()=>array('value'=>$this->getChecked(),'operator'=>'IN')));
			if($list = $dao->fetchAll($criteria)){
				$return = $list;
			}
		}
		return $return;
	}

	public function populateValue($values){
		if($values instanceof \Smally\ContextStdClass) $values = $values->toArray();
		if(!is_array($values)) $values = array($values);
		foreach($values as $value){
			$this->_checked[] = $value;
		}
		return $this;
	}

}