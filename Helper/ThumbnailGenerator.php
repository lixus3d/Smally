<?php

namespace Smally\Helper;

class ThumbnailGenerator {

	protected $_application = null;

	protected $_params = array(
			'x' => 400,
			'y' => null,
			't' => 'fit'
		);
	protected $_filePath = null;

	public function __construct($filePath==null){
		if(!is_null($filePath)){
			$this->setFilePath($filePath);
		}
	}

	/**
	 * Set the file path the thumbnail generator must use for generation
	 * @param string $filePath A valid filepath to an image file
	 * @return  \Smally\Helper\ThumbnailGenerator
	 */
	public function setFilePath($filePath){
		if(file_exists($filePath)){
			$this->_filePath
		}else throw new \Smally\Exception('Invalid filepath given for the ThumbnailGenerator.');
		return $this;
	}


	/**
	 * Set the params of the thumbnail
	 * @param array  $params The params you want for the thumbnail
	 * @param boolean $reset  Do we reset the actual params ? ( if we just give an x500 , so it will keep y => null and t => fit)
	 * @return  \Smally\Helper\ThumbnailGenerator
	 */
	public function setParams($params, $reset = false){
		if($reset){
			$this->_params = $params;
		}else{
			$this->_params = array_merge($this->_params,$params);
		}
		return $this;
	}

	/**
	 * Set the params of the thumbnail from a paramsString
	 * @param string $paramsString The paramsString you want to be parse and add as params
	 * @return  \Smally\Helper\ThumbnailGenerator
	 */
	public function setParamsFromString($paramsString){
		return $this->setParams($this->parseParamsString($paramsString));
	}

	/**
	 * Get the current Smally Application
	 * @return \Smally\Application
	 */
	public function getApplication(){
		if(is_null($this->_application)){
			$this->_application = \Smally\Application::getInstance();
		}
		return $this->_application;
	}

	/**
	 * Return the file extension ( from _filePath )
	 * @return string
	 */
	public function getFileExtension(){
		return strtolower(substr(strrchr($this->_filePath,'.'),1));
	}

	/**
	 * Parse a params string to array
	 * Keep in mind that the string must contain a k[0-9]+ validation key to parse
	 * @param  string $string The params string you want to convert (in the format : x555-y333-tFill-k34 )
	 * @return array An array representation of the given $string params
	 */
	public function parseParamsString($string){

		$params = array();
		$validKey = null;

		$parts = explode('-',$string);
		foreach($parts as $part){
			if(strlen($part)>=2){
				$key = $part[0];
				$value = substr($part,1);
				switch($key){
					case 'x':
					case 'y':
						$params[$key] = (int) $value;
						break;
					case 't':
						if(preg_match('#^fit|fit+|fill|stretch$#i',$value)){
							$params[$key] = (string) $value;
						}
						break;
					case 'k':
						$validKey = (int) $value;
						break;
					default:
						throw new \Smally\Exception('Invalid thumbnail param key.');
				}
			}else throw new \Smally\Exception('Invalid thumbnail param format.');
		}

		if($validKey !== $this->generateKey($params)) throw new \Smally\Exception('Invalid thumbnail validation key');

		return $params;
	}

	/**
	 * Generate a params validation key
	 * @param  array $params The array of params you want to generate a key for
	 * @return string
	 */
	public function generateKey($params){
		$key = serialize($params);
		$key = md5($key);
		$key = array_sum(str_split(preg_replace('#[^2-9]#','',$key)));
		$key = $key % 79;
		return $key;
	}

	/**
	 * Get the path of a thumbnail with this $params
	 * @param  array $params The params of the thumbnail
	 * @return string
	 */
	public function constructParamsString($params){
		$params['k'] = $this->generateKey($params);
		$string = array();
		foreach($params as $key => $value){
			$string[] = $key.$value;
		}
		$string = implode('-',$string);
		return $string;
	}

	/**
	 * Create the thumbnail of the current $file with the given $params
	 * @return \Smally\Helper\ThumbnailGenerator
	 */
	public function create(){

		$extension = $this->getFileExtension();

		switch($extension){
			case 'jpg':
			case 'jpeg':
				$img = imagecreatefromjpeg($name);
				break;
			case 'gif':
				$img = imagecreatefromgif($name);
				break;
			case 'png':
				$img = imagecreatefrompng($name);
				break;
			default:
				throw new Exception('Invalid file extension for thumbnail.');
		}
	}

}