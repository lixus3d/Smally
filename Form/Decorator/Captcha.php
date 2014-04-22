<?php

namespace Smally\Form\Decorator;

class Captcha extends AbstractDecorator {

	/**
	 * Render the input Decorator, work for type : text, password, checkbox, radio, submit
	 * @param  string $content Existing generated content
	 * @return string
	 */
	public function render($content){

		$attributes = array(
				'name' => $this->getElement()->getName(),
				'type' => $this->getElement()->getType(),
				'value' => $this->getElement()->getValue(),
			);
		$placeholder = $this->getElement()->getPlaceholder();
		$default = $this->getElement()->getDefault();

		if($placeholder !== '') $attributes['placeholder'] = $placeholder;
		if($default !== '' && $attributes['value'] == '') $attributes['value'] = $default;

		$attributes = array_merge($attributes,$this->_element->getAttributes());

		$html = '<div class="input">';
		$html  = $this->getForm()->getDecorator('error',$this->_element)->render($html);
		$html .= '
				<script type="text/javascript">
				var RecaptchaOptions = {
					theme : \'clean\',
					lang : \'fr\',
				};
				</script>
				 ';

		require_once(VENDORS_PATH.'/recaptcha/recaptchalib.php');
		$html .= recaptcha_get_html((string)\Smally\Application::getInstance()->getConfig()->google->recaptcha->key->public);

		$html  = $this->getForm()->getDecorator('help',$this->_element)->render($html);
		$html .= '</div>';

		return $this->concat($html,$content);
	}

}