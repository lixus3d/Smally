<?php

namespace Smally\Helper\Decorator\MenuIcon;

class MenuElement extends \Smally\Helper\Decorator\MenuElement {

	/**
	 * Render the menu decorator
	 * @param  string $content Existing generated content
	 * @return string
	 */
	public function render($content=''){

		if(method_exists($this, 'onRender')){
			$this->{'onRender'}();
		}

		// Switch on element type
		switch($this->getElement()->getType()){
			case 'separator':
				$this->getElement()->setAttribute('class','divider');
				$html  = '<li'.\Smally\HtmlUtil::toAttributes($this->getAttributes()).'>';
				$html .= '<span><span class="icon"></span>';
				$html .= '<span class="text">';
				$html .= '</span>';
				$html .= '</span>';
				$html .= '</li>' . NN;
			break;
			case 'header':
				$this->getElement()->setAttribute('class','nav-header');
				$html  = '<li'.\Smally\HtmlUtil::toAttributes($this->getAttributes()).'>';
				$html .= '<span><span class="icon"></span>';
				$html .= '<span class="text">';
				$html .= $this->getInnerHtml();
				$html .= '</span>';
				$html .= '</span>';
				$html .= '</li>' . NN;
			break;
			default:
			case 'page':
				// we render children before to adapt attributes if necessary (active class for example, or hasChildren)
				$children = $this->renderChildren();
				$html  = '<li'.\Smally\HtmlUtil::toAttributes($this->getAttributes()).'>';
				$html .= '<a href="'.$this->getElement()->getUrl().'" '.\Smally\HtmlUtil::toAttributes($this->_attributesA).'>';
				$html .= '<span><span class="icon"></span>';
				$html .= '<span class="text">';
				$html .= $this->getInnerHtml();
				$html .= '</span>';
				$html .= '</span>';
				$html .= '</a>';
				$html .= $children;
				$html .= '</li>' . NN;
			break;
		}
		return $this->concat($html,$content);
	}

}