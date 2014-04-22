<?php

namespace Smally\Validator;

class Captcha extends AbstractRule {

	/**
	 * Validate if the $valueToTest is an email
	 * @param  mixed $valueToTest
	 * @return boolean
	 */
	public function x($valueToTest){

		require_once(VENDORS_PATH.'/recaptcha/recaptchalib.php');
		$resp = recaptcha_check_answer ((string)\Smally\Application::getInstance()->getConfig()->google->recaptcha->key->private,
			$_SERVER["REMOTE_ADDR"],
			$_POST["recaptcha_challenge_field"],
			$_POST["recaptcha_response_field"]);

		if (!$resp->is_valid) {
			$this->addError(__('VALIDATOR_CAPTCHA_ERROR'));
			return false;
		} else {
			return true;
		}
	}

}