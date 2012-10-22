<?php

namespace Smally;

class Mailer {

	protected $_application = null;

	protected $_mailerClass = null;

	/**
	 * Construct a Mailer object
	 * @param \Smally\Application $application reverse reference to the application
	 */
	public function __construct(\Smally\Application $application){
		$this->setApplication($application);
	}

	/**
	 * Set the application reverse reference
	 * @param \Smally\Application $application Current application linked to this object
	 * @return \Smally\Controller
	 */
	public function setApplication(\Smally\Application $application){
		$this->_application = $application;
		return $this;
	}

	/**
	 * Return the application reverse referenced
	 * @return \Smally\Application
	 */
	public function getApplication(){
		return $this->_application;
	}

	/**
	 * Get the mailer class, actually just PHPMailer is valid
	 * @return \Smally\Mailer\PHPMailer
	 */
	public function getMailerClass(){
		if(is_null($this->_mailerClass)){
			$mail = new \Smally\Mailer\PHPMailer(true);
			$mail->IsSMTP(); // telling the class to use SMTP
			$mail->SMTPDebug  = 1;                     // enables SMTP debug information (for testing), 1 = errors and messages, 2 = messages only
			$mail->Host       = (string)$this->getApplication()->getConfig()->smally->mailer->host?:'smtp.domain.com'; // SMTP server
			$mail->Port       = (string)$this->getApplication()->getConfig()->smally->mailer->port?:25;                    // set the SMTP port for the GMAIL server
			if($this->getApplication()->getConfig()->smally->mailer->SMTPAuth === true){
				$mail->SMTPAuth   = true;                  // enable SMTP authentication
				$mail->Username   = (string)$this->getApplication()->getConfig()->smally->mailer->username?:'email@domain.com'; // SMTP account username
				$mail->Password   = (string)$this->getApplication()->getConfig()->smally->mailer->password?:'password';        // SMTP account password
			}
			$this->_mailerClass = $mail;
		}
		return $this->_mailerClass;
	}

	/**
	 * Wrapper of the MailerClass instance so you can access every class functions easily
	 * @param  string $name method called
	 * @param  array $args arguments
	 * @return mixed Application method return
	 */
	public function __call($name,$args){
		if(method_exists($this->getMailerClass(), $name)){
			return call_user_func_array(array($this->getMailerClass(),$name), $args);
		}else throw new Exception('Call to undefined method : '.$name);
		return null;
	}

	/**
	 * Wrapper to the Subject property
	 * @param string $subject The subject you want for your email
	 * @return boolean
	 */
	public function setSubject($subject){
		return ($this->getMailerClass()->Subject = $subject)?true:false;
	}

	/**
	 * Wrapper to the AltBody property
	 * @param string $body The text body you want for your email
	 * @return boolean
	 */
	public function MsgText($body){
		return ($this->getMailerClass()->AltBody = $body)?true:false;
	}


	/**
	 * Override AddXxxx functions of PHPMailer to set a dev recipient when not in Production environnement
	 */
	public function AddAddress($address, $name = '') {return $this->AddAnAddress('to', $address, $name);}
	public function AddCC($address, $name = '') {return $this->AddAnAddress('cc', $address, $name);}
	public function AddBCC($address, $name = '') {return $this->AddAnAddress('bcc', $address, $name);}
	public function AddReplyTo($address, $name = '') {return $this->AddAnAddress('ReplyTo', $address, $name);}

	/**
	 * Wrapper to the AddAnAddress to override with a dev recipient in other environnement than production
	 * Refer to PHPMailer doc for more information
	 */
	public function AddAnAddress($kind, $address, $name = ''){
		if($this->getApplication()->getEnvironnement()!==\Smally\Application::ENV_PRODUCTION){
			if( $recipient = $this->getApplication()->getConfig()->smally->mailer->dev->recipient->toArray() ){
				$address = $recipient[0];
				$name = $recipient[1];
			}else throw new \Smally\Exception('You must define a default address when not in production environnement. In the config file , set smally->mailer->dev->recipient = array(\'email\',\'name\') ');
		}
		return $this->getMailerClass()->AddAnAddress($kind,$address,$name);
	}

}