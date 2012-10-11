<?php

namespace Smally;

/**
 * Generic Logger class for all your logs
 */

class Logger {

	const LVL_INFO 		= 1;
	const LVL_DEBUG 	= 2;
	const LVL_WARNING 	= 3;
	const LVL_ERROR 	= 4;

	const DEST_LOG 		= 1;
	const DEST_MYSQL 	= 2;
	const DEST_MAIL		= 4;
	const DEST_CONSOLE	= 8;
	const DEST_SMS		= 16;

	static $typeLabel = array(
		self::LVL_INFO 		=> 'INFO',
		self::LVL_DEBUG 	=> 'DEBUG',
		self::LVL_WARNING 	=> 'WARNING',
		self::LVL_ERROR 	=> 'ERROR',
	);

	static protected $_singleton 	= null;

	protected $_application = null;

	protected $_logPath = '';
	protected $_logPathFile = array();
	protected $_separator = ' | ';


	public function __construct($logPath='',$application=null){
		$this->setLogPath($logPath);
		if($application instanceof \Smally\Application){
			$this->_application = $application;
		}
	}

	/**
	 * Define the current logger to be the singleton of Logger (default one)
	 * @return \Smally\Logger
	 */
	public function setInstance(){
		return self::$_singleton = $this;
	}

	/**
	 * Set the log directory
	 * @param string $path A valid directory
	 * @return \Smally\Logger
	 */
	public function setLogPath($path){
		if(is_dir($path)){
			$this->_logPath = $path;
		}else throw new Exception('Invalid log path given : '.$path);
		return $this;
	}

	/**
	 * Define a specific file $name for the given $type
	 * @param int $type Type of destination
	 * @param string $name The name of the destination file
	 * @return \Smally\Logger
	 */
	public function setLogTypeFile($type,$name){
		$this->_logPathFile[$type] = $name;
		return $this;
	}

	/**
	 * Return the singleton of the default Logger, a setInstance must have been called before
	 * @return \Smally\Logger
	 */
	public function getInstance(){
		return self::$_singleton; // can return null in case of no setInstance done before
	}

	/**
	 * Return a filePath for the given $destinationType
	 * @param  int $destinationType A destination type (constant of Logger)
	 * @return string
	 */
	public function getFilePath($destinationType){
		if(!isset($this->_logPathFile[$destinationType])){
			$path = $this->_logPath;
			switch($destinationType){
				case self::DEST_LOG:
					$path .= 'log-'.date('Y-m').'.log';
					break;
				case self::DEST_MYSQL:
					$path .= 'mysql-'.date('Y-m').'.log';
					break;
			}
			$this->_logPathFile[$destinationType] = $path;
		}
		return $this->_logPathFile[$destinationType];
	}

	/**
	 * Return the logLevel defined for a given $module or the default one if $module is null
	 * @param  string $module the module name
	 * @return int
	 */
	public function getLogLevel($module=null){
		if(!is_null($this->_application)){
			$level = $this->_application->getConfig()->smally->logger->level->{$module};
			if($level == ''){
				$level = $this->_application->getConfig()->smally->logger->level->default;
			}
			if($level != '') return $level;
		}
		return 100;
	}

	/**
	 * Log a $text of a given $level to the given $destination
	 * @param  string $text        The text to log , array are converted with print_r
	 * @param  int $level       the level of the text to log
	 * @param  int $destination destination of the log, bitfield so you can log to multiple destination
	 */
	public function log($text='',$level=self::LVL_INFO,$destination=self::DEST_LOG){

		if(is_array($text)) $text = print_r($text,true);

		$typeLabel = isset(self::$typeLabel[$level])?self::$typeLabel[$level]:'OTHER';
		$typeLabel[7] = ' ';

		$completeText = date('Y-m-d H:i:s'). $this->_separator .$typeLabel. $this->_separator .$text;

		$return = false;
		for($i=0;$i<=5;$i++){
			$bit = pow(2,$i) & $destination ;
			switch(true){
				case $bit&self::DEST_LOG:
					$return = $this->writeToFile($completeText,$this->getFilePath(self::DEST_LOG));
					break;
				case $bit&self::DEST_MYSQL:
					$return = $this->writeToFile($completeText,$this->getFilePath(self::DEST_MYSQL));
					break;
				case $bit&self::DEST_MAIL:
					// TODO : get the email from config or default server contact email
					$return = $this->writeToMail($completeText,'developpement@adreamaline.com');
					break;
				case $bit&self::DEST_CONSOLE:
					$return = $this->writeToConsole($completeText);
					break;
				case $bit&self::DEST_SMS:
					// TODO : get the phone number from the config
					$return = $this->writeToSms($completeText,'0102030405');
					break;
			}
		}

		if(!$return){
			error_log('Logger : can\'t write to log !');
			error_log('Logger : '.$completeText);
		}

		return $return;
	}


	/**
	 * Write a $text into a file at given $filePath
	 * @param  string $text     The text to write
	 * @param  string $filePath The file path
	 * @return boolean
	 */
	public function writeToFile($text,$filePath){
		return file_put_contents($filePath, $text.RN , FILE_APPEND );
	}

	/**
	 * Write a $text into an email for $destinationEmail
	 * @param  string $text             The text to write/send
	 * @param  string $destinationEmail A valid email adress
	 * @return boolean
	 */
	public function writeToMail($text,$destinationEmail){
		// TODO : Do the write to mail method
		return true;
	}

	/**
	 * Write a $text to the firephp console
	 * @param  string $text The text to write/send to the console
	 * @return boolean
	 */
	public function writeToConsole($text){
		// TODO : Do the write to console method
		return true;
	}

	/**
	 * Write a $text into a sms for $destinationNumber
	 * @param  string $text              The text to write/send
	 * @param  string $destinationNumber A valid phone number
	 * @return boolean
	 */
	public function writeToSms($text,$destinationNumber){
		// TODO : Do the write to sms method
		return true;
	}



}