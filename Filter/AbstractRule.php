<?php

namespace Smally\Filter;

abstract class AbstractRule implements InterfaceRule {

	protected $_fieldName = null;
	protected $_filter = null;

	/**
	 * Define the field name the rule will be on
	 * @param string $fieldName The field name
	 * @return  \Smally\Filter\AbstractRule
	 */
	public function setFieldName($fieldName){
		$this->_fieldName = $fieldName;
		return $this;
	}

	/**
	 * Define the back reference to the actual filter
	 * @param \Smally\Filter $filter A valid \Smally\Filter object
	 * @return  \Smally\Filter\AbstractRule
	 */
	public function setFilter($filter){
		$this->_filter = $filter;
		return $this;
	}

	/**
	 * Get the fieldname associated with the rule
	 * @return string
	 */
	public function getFieldName(){
		return $this->_fieldName;
	}

	/**
	 * Get the back reference to the filter
	 * @return \Smally\Filter
	 */
	public function getFilter(){
		return $this->_filter;
	}

}