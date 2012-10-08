<?php

namespace Smally\Helper\Decorator;

class Paging extends AbstractDecorator {

	/**
	 * Return the element (paging) attributes
	 * @return array
	 */
	public function getAttributes(){
		return $this->getElement()->getAttributes();
	}

	/**
	 * Render the paging decorator
	 * Usually the only one method redefined in another Paging Decorator
	 * @param  string $content Existing generated content
	 * @return string
	 */
	public function render($content=''){
		// we render children before to adapt attributes if necessary (active class for example, or hasChildren)
		if($this->getElement()->getNbPages()>1){
			$children = $this->renderChildren();
			$html  = '<ul'.\Smally\HtmlUtil::toAttributes($this->getAttributes()).'>' . NN;
			$html .= $children;
			$html .= '</ul>' . NN;
		}else $html = '';
		return $this->concat($html,$content);
	}


	/**
	 * Return the render of the current element sub elements (page number / arrow)
	 * @return string
	 */
	public function renderChildren(){
		$html = '';

		for($i=1;$i<=$this->getElement()->getNbPages();$i++){
			$html .= $this->getElement()->getDecorator('pagingElement',$i)
											->setPaging($this->getElement())
											->setElementNumber($i,$this->getElement()->getNbPages())
											->render()
											;
		}

		return $html;
	}
}