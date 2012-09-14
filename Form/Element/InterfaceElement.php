<?php

namespace Smally\Form\Element;

/**
 * Interface to use for your own element
 */
interface InterfaceElement {
	public function setType($type);
	public function setName($name);
	public function setLabel($label);
	public function setValue($value);
	public function setHelp($help);
	public function setError($error);
	public function setForm(\Smally\Form $form);
	public function render();
}