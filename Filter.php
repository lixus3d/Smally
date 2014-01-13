<?php

namespace Smally;

class Filter {

	const MODE_NEW = 0;
	const MODE_EDIT = 1;

	protected $_mode = self::MODE_NEW;
	protected $_testValues = array();
	protected $_rules = array();

	protected $_actualVoId = null;

	/**
	 * You can pass directly the values to filter to the constructor
	 * @param array $testValues the key values array you want to filter
	 */
	public function __construct($testValues=array(),$mode=self::MODE_NEW){
		$this->setMode($mode);
		$this->setTestValues($testValues);

		if(method_exists($this, 'init')){
			$this->init();
		}
	}

	/**
	 * Define the mode of the filter new or edit
	 * @param int $mode The mode you want , use class constants
	 * @return  \Smally\Filter
	 */
	public function setMode($mode){
		$this->_mode = $mode;
		return $this;
	}

	/**
	 * Set the testValues of the filter
	 * @param array  $testValues The values to test
	 * @return  \Smally\Filter
	 */
	public function setTestValues($testValues){
		$this->_testValues = $testValues;
		return $this;
	}

	/**
	 * Define the id of the actual object edited
	 * @param int $actualVoId The id
	 * @return  \Smally\Filter
	 */
	public function setActualVoId($actualVoId){
		$this->_actualVoId = $actualVoId;
		return $this;
	}

	/**
	 * Set the value of a particular field
	 * @param string $fieldName  The field name you want the value to set
	 * @param mixed $fieldValue The value you want to set
	 * @return  \Smally\Filter
	 */
	public function setValue($fieldName,$fieldValue){
		$this->_testValues[$fieldName] = $fieldValue;
		return $this;
	}

	/**
	 * Add a filter rule to the field $field
	 * @param string                          $field      The name of the field to test
	 * @param \Smally\Filter\InterfaceRule $ruleObject The rule object to apply to the field
	 * @return \Smally\Filter
	 */
	public function addRule($field,\Smally\Filter\InterfaceRule $ruleObject){
		if(!isset($this->_rules[$field])) $this->_rules[$field] = array();
		$this->_rules[$field][] = $ruleObject;
		$ruleObject->setFieldName($field);
		$ruleObject->setFilter($this);
		return $this;
	}

	/**
	 * Return the filter mode (new or edit)
	 * @return int Use it to compare with class constants
	 */
	public function getMode(){
		return $this->_mode;
	}

	/**
	 * Return a test value by it's fieldName
	 * @param  string $fieldName The fieldName of the value you want to test
	 * @return mixed
	 */
	public function getValue($fieldName){
		return isset($this->_testValues[$fieldName])?$this->_testValues[$fieldName]:null;
	}

	/**
	 * Return filters defined for a particular field
	 * @param  string $fieldName The name of the field you want filters from
	 * @return array Array of \Smally\Filter\AbstractRule or null if no rules
	 */
	public function getFieldRules($fieldName){
		return isset($this->_rules[$fieldName]) ? $this->_rules[$fieldName] : null;
	}

	/**
	 * Get the actual vo id of the object currently edited
	 * @return int
	 */
	public function getActualVoId(){
		return $this->_actualVoId;
	}

	/**
	 * Execute the filter rules on $fields
	 * @return boolean
	 */
	public function x($testValues=null){
		if(is_array($testValues)) $this->setTestValues($testValues);
		foreach($this->_rules as $field => $rules){
			foreach($rules as $rule){
				$fieldValue = $this->getValue($field);
				$this->setValue($field,$rule->x($fieldValue));
			}
		}
		return $this->_testValues;
	}







}