<?php

namespace Smally\Helper;

class ThumbnailGenerator {

	protected $_application = null;

	protected $_params = array();

	protected $_filePath = null;
	protected $_thumbnailPath = null;

	public function __construct($filePath=null){
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
		if(SMALLY_PLATFORM=='windows') $filePath = utf8_decode($filePath);
		if(file_exists($filePath)){
			$this->_filePath = $filePath;
		}else{
			//throw new \Smally\Exception('Invalid filepath given for the ThumbnailGenerator : '.$filePath);
		}
		return $this;
	}


	/**
	 * Set the params of the thumbnail
	 * @param array  $params The params you want for the thumbnail
	 * @return  \Smally\Helper\ThumbnailGenerator
	 */
	public function setParams($params){
		$this->_params = $params;
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
	 * Return the thumbnail file path , constructed from the filepath given
	 * @return string
	 */
	public function getThumbnailPath($mkdir=false){
		if(is_null($this->_thumbnailPath)||$mkdir){
			$basePath = dirname($this->_filePath).DIRECTORY_SEPARATOR.'thumbnail'.DIRECTORY_SEPARATOR.self::constructParamsString($this->_params);
			if($mkdir){
				$this->makePath($basePath);
			}
			$this->_thumbnailPath = $basePath.DIRECTORY_SEPARATOR.basename($this->_filePath);
		}
		return $this->_thumbnailPath;
	}

	/**
	 * Use makePath to create directory on disk of a $path
	 * @param  string $path     The path you want to create
	 * @return null
	 */
	public function makePath($path){
		$pathParts = explode(DIRECTORY_SEPARATOR,$path);
		$path = '';
		foreach($pathParts as $part){
			$path .= $part;
			if(!is_dir($path)){
				mkdir($path);
				chmod($path,0777);
			}
			$path .= DIRECTORY_SEPARATOR;
		}
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
							$params[$key] = strtolower($value);
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

		if($validKey !== $this->generateKey($params)) throw new \Smally\Exception('Invalid thumbnail validation key : '.$this->generateKey($params));

		return $params;
	}

	/**
	 * Generate a params validation key
	 * @param  array $params The array of params you want to generate a key for
	 * @return string
	 */
	static public function generateKey($params){
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
	static public function constructParamsString($params=array()){
		if(isset($params['k'])) unset($params['k']);
		$string = array();
		foreach($params as $key => $value){
			if(is_null($value)){
				unset($params[$key]);
				continue;
			}
			$string[] = $key.$value;
		}
		$string[] = 'k'.self::generateKey($params);
		$string = implode('-',$string);
		return $string;
	}

	/**
	 * Create the thumbnail of the current $file with the given $params
	 * @return \Smally\Helper\ThumbnailGenerator
	 */
	public function create(){

		if(file_exists($this->getThumbnailPath())){
			return $this;
		}

		list($width,$height,$type,$attr) = getimagesize($this->_filePath);

		$params = &$this->_params;
		$destWidth = isset($params['x'])?$params['x']:400;
		$destHeight = isset($params['y'])?$params['y']:null;
		$destType =  isset($params['t'])?$params['t']:'fit';

		// There is no need to create a thumbnail, just copy the original to the destination
		if($destWidth>$width && $destHeight>$height && $destType=='fit'){
			copy($this->_filePath,$this->getThumbnailPath(true));
			return $this;
		}

		// Create the original image in GD
		$img = $this->getGdImage($this->_filePath);

		// Create the thumbnail image
		$thbImg = $this->$destType($img,$width,$height,$destWidth,$destHeight);

		// Write the thumbnail image to disk
		$this->writeImage($thbImg);

		// Destroy GD image instances
		imagedestroy($img);
		imagedestroy($thbImg);

		return $this;
	}

	/**
	 * Get the GD image instance of the current file
	 * @return int
	 */
	public function getGdImage(){

		$extension = $this->getFileExtension();

		switch($extension){
			case 'jpg':
			case 'jpeg':
				$function = 'imagecreatefromjpeg';
				break;
			case 'png':
			case 'gif':
				$function = 'imagecreatefrom'.$extension;
				break;
			default:
				throw new Exception('Invalid file extension for thumbnail.');
		}

		// Create the original image in GD from the type
		return $function($this->_filePath);
	}

	/**
	 * Fit an $img in the given $destWidth and $destHeight
	 * @param  integer  $img        The original image (GD image id )
	 * @param  integer  $width      The original image width ( or portion width if $x is given )
	 * @param  integer  $height     The original image height ( or portion height if $x is given )
	 * @param  integer  $destWidth  The destination max width, can be null if determine by $destHeight and original image ratio
	 * @param  integer  $destHeight The destination max height, can be null if determine by $destWidth and original image ratio
	 * @param  integer $x          Original image portion left position
	 * @param  integer $y          Original image portion top position
	 * @return interger A new GD image id
	 */
	public function fit($img,$width,$height,$destWidth,$destHeight,$x=0,$y=0){
		if(is_null($destWidth)&&is_null($destHeight)){
			throw new \Smally\Exception('Either destWidth or destHeight must be defined. Null for both given.');
		}

		// calculate image ratio
		$ratio = $width / $height ;

		// Finalize destination size , if one of them is null it match the ratio of the given source
		if(is_null($destHeight)) $destHeight = (int) ($destWidth / $ratio);
		elseif(is_null($destWidth)) $destWidth = (int) ($destHeight * $ratio);

		$destRatio = $destWidth / $destHeight;

		if($ratio > $destRatio){
			$destHeight = (int) $destWidth / $ratio;
		}elseif($ratio < $destRatio) {
			$destWidth = (int) $destHeight*$ratio;
		}

		$thb = imagecreatetruecolor($destWidth,$destHeight);
		imagealphablending($thb, false);
		imagesavealpha($thb, true);
		imagecopyresampled($thb, $img, 0, 0, $x, $y, $destWidth, $destHeight, $width, $height);

		return $thb;
	}

	/**
	 * Fill an $img in the given $destWidth and $destHeight, the destination will be of $destWidth and $destHeight size
	 * @param  integer  $img        The original image (GD image id )
	 * @param  integer  $width      The original image width ( or portion width if $x is given )
	 * @param  integer  $height     The original image height ( or portion height if $x is given )
	 * @param  integer  $destWidth  The destination max width, cant be null
	 * @param  integer  $destHeight The destination max height, cant be null
	 * @param  integer $x          Original image portion left position
	 * @param  integer $y          Original image portion top position
	 * @return interger A new GD image id
	 */
	public function fill($img,$width,$height,$destWidth,$destHeight,$x=null,$y=null){
		if(is_null($destWidth)||is_null($destHeight)){
			throw new \Smally\Exception('Neither destWidth or destHeight can be null.');
		}

		$thb = imagecreatetruecolor($destWidth,$destHeight);
		imagealphablending($thb, false);
		imagesavealpha($thb, true);

		// calculate image ratio
		$ratio = $width / $height ;
		$destRatio = $destWidth / $destHeight;

		if($ratio > $destRatio){
			$heightMulti = $destHeight / $height ;
		}elseif($ratio < $destRatio) {

		}

		imagecopyresampled($thb, $img, 0, 0, $x, $y, $destWidth, $destHeight, $width, $height);

		return $thb;
	}

	public function stretch($img,$width,$height,$destWidth,$destHeight,$x=0,$y=0){

	}

	/**
	 * Write a gd thumbnail image to disk
	 * @param  int  $img     GD image id
	 * @param  integer $quality The quality you want for the file
	 * @return boolean
	 */
	public function writeImage($img,$quality=95){

		$extension = $this->getFileExtension();
		$filename = $this->getThumbnailPath(true);

		switch($extension){
			case 'jpg':
			case 'jpeg':
				imagejpeg($img, $filename, $quality);
				break;
			case 'gif':
				imagegif($img, $filename);
				break;
			case 'png':
				imagepng($img, $filename);
				break;
			default:
				throw new Exception('Invalid file extension for thumbnail.');
		}

		return true;
	}

}