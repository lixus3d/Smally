<?php

namespace Smally\Helper;

class ThumbnailGenerator {

	protected $_application = null;

	protected $_params = array();

	protected $_filePath = null;
	protected $_extension = null;
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
	 * Set the file extension, usefull when we can't get it from path
	 * @param string $extension The extension of the file
	 * @return  \Smally\Helper\ThumbnailGenerator
	 */
	public function setExtension($extension){
		$this->_extension = $extension;
		return $this;
	}

	/**
	 * Set the default options, usefull for cropping
	 * @param string $extension The file options
	 * @return  \Smally\Helper\ThumbnailGenerator
	 */
	public function setDefaultOptions($options){
		$this->_defaultOptions = $options;
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
	 * Override the thumbnail destination path
	 * @param string $thumbnailPath A complete path with the filename
	 * @return  \Smally\Helper\ThumbnailGenerator
	 */
	public function setThumbnailPath($thumbnailPath){
		$this->_thumbnailPath = $thumbnailPath;
		return $this;
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
	 * Return the file extension ( from _filePath if not set )
	 * @return string
	 */
	public function getFileExtension(){
		if(is_null($this->_extension)){
			$this->_extension = strtolower(substr(strrchr($this->_filePath,'.'),1));
		}
		return $this->_extension;
	}

	/**
	 * Return the thumbnail file path , constructed from the filepath given
	 * @return string
	 */
	public function getThumbnailPath($mkdir=false){
		if(is_null($this->_thumbnailPath)){
			$basePath = dirname($this->_filePath).DIRECTORY_SEPARATOR.'thumbnail'.DIRECTORY_SEPARATOR.self::constructParamsString($this->_params);
			$this->_thumbnailPath = $basePath.DIRECTORY_SEPARATOR.basename($this->_filePath);
		}
		if($mkdir){
			$this->makePath(dirname($this->_thumbnailPath));
		}
		return $this->_thumbnailPath;
	}

	/**
	 * Use makePath to create directory on disk of a $path
	 * @param  string $path     The path you want to create
	 * @return null
	 */
	public function makePath($originPath){
		$path = str_replace(array('/','\\'),DIRECTORY_SEPARATOR,$originPath);
		$pathParts = explode(DIRECTORY_SEPARATOR,$path);
		$path = '';
		foreach($pathParts as $part){
			$path .= $part;
			if($path != ''){
				if(!@is_dir($path)){
					if(@mkdir($path)){
						@chmod($path,0777);
					}else{
						$this->getApplication()->getLogger()->log('Error in makePath : '.$originPath.' , '.$path);
					}
				}
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
					case 'c': // use crop
					case 'x': // destWidth
					case 'y': // destHeight
					case 'u': // left
					case 'v': // top
					case 'w': // srcWidth
					case 'h': // srcHeight
						$params[$key] = (int) $value;
						break;
					case 't':
						if(preg_match('#^fit|fitplus|fill|stretch$#i',$value)){
							$params[$key] = strtolower($value);
						}
						break;
					case 'b':
						if(preg_match('#^[A-F0-9]{6}$#i',$value)){
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
		$params = ksort($params);
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

		$params = &$this->_params;
		$params = array_merge($this->_defaultOptions,$params);


		list($width,$height,$type,$attr) = getimagesize($this->_filePath);
		if(is_null($width)) $width = 256;
		if(is_null($height)) $height = 256;

		// Crop params
		if( isset($params['c']) && $params['c'] == 1 && isset($params['w']) && isset($params['h']) && isset($params['u']) && isset($params['v'])){
			$left = $params['u'] * $width;
			$top = $params['v'] * $height;
			$width = $params['w'] * $width;
			$height = $params['h'] * $height;
		}

		$destWidth = isset($params['x'])?$params['x']:null;
		$destHeight = isset($params['y'])?$params['y']:null;
		$destType =  isset($params['t'])?$params['t']:'fit';

		if( is_null($destWidth) && is_null($destHeight) ){
			$destWidth = 256;
		}

		// There is no need to create a thumbnail, just copy the original to the destination if dest size greater and mode to fit
		if( ( is_null($destWidth) || $destWidth>$width ) && ( is_null($destHeight) || $destHeight>$height ) && $destType=='fit'){
			if(!isset($top)){ // if the source is not cropped , symply copy the original
				copy($this->_filePath,$this->getThumbnailPath(true));
				chmod($this->getThumbnailPath(true),0777);
				return $this;
			}else{
				if( $destWidth ) $destWidth = $width;
				else $destHeight = $height;
			}
		}

		// Create the original image in GD
		$img = $this->getGdImage($this->_filePath);

		// Create the thumbnail image
		if(isset($top)){
			$thbImg = $this->$destType($img,$width,$height,$destWidth,$destHeight,$left,$top);
		}else{
			$thbImg = $this->$destType($img,$width,$height,$destWidth,$destHeight);
		}

		// Write the thumbnail image to disk
		$this->writeImage($thbImg);

		// Destroy GD image instances
		imagedestroy($img);
		imagedestroy($thbImg);

		return $this;
	}

	/**
	 * Get the GD image instance of the current file
	 * TODO : set default icon path in config
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
			case 'pdf':
				return imagecreatefrompng(ROOT_PATH.'public/assets/img/default-icon-pdf.png');
			case 'doc':
			case 'docx':
			case 'odt':
				return imagecreatefrompng(ROOT_PATH.'public/assets/img/default-icon-word.png');
			default:
				return imagecreatefrompng(ROOT_PATH.'public/assets/img/default-icon-file.png');
		}

		// Create the original image in GD from the type
		return $function($this->_filePath);
	}

	/**
	 * Fit an $img in the given $destWidth and $destHeight, the destination is not necessary of $destWidth and $destHeight size
	 * @param  integer  $img        The original image (GD image id )
	 * @param  integer  $width      The original image width ( or portion width if $x is given )
	 * @param  integer  $height     The original image height ( or portion height if $x is given )
	 * @param  integer  $destWidth  The destination max width, can be null if determine by $destHeight and original image ratio
	 * @param  integer  $destHeight The destination max height, can be null if determine by $destWidth and original image ratio
	 * @param  integer $x          Original image portion left position
	 * @param  integer $y          Original image portion top position
	 * @return integer A new GD image id
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
	 * Fit an $img in the given $destWidth and $destHeight, the destination is of $destWidth and $destHeight size.
	 * $backgroundColor is use to fill gap due to different ratios
	 * @param  integer  $img        The original image (GD image id )
	 * @param  integer  $width      The original image width ( or portion width if $x is given )
	 * @param  integer  $height     The original image height ( or portion height if $x is given )
	 * @param  integer  $destWidth  The destination max width, can be null if determine by $destHeight and original image ratio
	 * @param  integer  $destHeight The destination max height, can be null if determine by $destWidth and original image ratio
	 * @param  integer $x          Original image portion left position
	 * @param  integer $y          Original image portion top position
	 * @param  string $backgroundColor color to use for background, default is white
	 * @return integer A new GD image id
	 */
	public function fitplus($img,$width,$height,$destWidth,$destHeight,$x=0,$y=0,$backgroundColor=null){
		if(is_null($destWidth)&&is_null($destHeight)){
			throw new \Smally\Exception('Either destWidth or destHeight must be defined. Null for both given.');
		}

		// Get background color from the params if available
		if(isset($this->_params['b'])&&$this->_params['b']) $backgroundColor = $this->_params['b'];

		// calculate image ratio
		$ratio = $width / $height ;

		// Finalize destination size , if one of them is null it match the ratio of the given source
		if(is_null($destHeight)) $destHeight = (int) ($destWidth / $ratio);
		elseif(is_null($destWidth)) $destWidth = (int) ($destHeight * $ratio);

		$destRatio = $destWidth / $destHeight;
		$destX=0;
		$destY=0;
		$inDestWidth=$destWidth;
		$inDestHeight=$destHeight;

		if($ratio > $destRatio){
			$inDestHeight = (int) $destWidth / $ratio;
			$destY = ($destHeight - $inDestHeight) / 2;
			$inDestWidth = $destWidth;
			$destX = 0;
		}elseif($ratio < $destRatio) {
			$inDestWidth = (int) $destHeight*$ratio;
			$destX = ($destWidth - $inDestWidth) / 2;
			$inDestHeight = $destHeight;
			$destY = 0;
		}

		$thb = imagecreatetruecolor($destWidth,$destHeight);
		imagealphablending($thb, false);
		imagesavealpha($thb, true);
		// set background color
		list($r,$g,$b) = $this->hex2rgb($backgroundColor?:'FFF');
		$backColor = imagecolorallocate($thb, $r, $g, $b);
		imagefill($thb, 0, 0,  $backColor );

		imagecopyresampled($thb, $img, $destX, $destY, $x, $y, $inDestWidth, $inDestHeight, $width, $height);

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
	 * @return integer A new GD image id
	 */
	public function fill($img,$width,$height,$destWidth,$destHeight,$x=0,$y=0){
		if(is_null($destWidth)||is_null($destHeight)){
			throw new \Smally\Exception('Neither destWidth or destHeight can be null.');
		}

		$thb = imagecreatetruecolor($destWidth,$destHeight);
		imagealphablending($thb, false);
		imagesavealpha($thb, true);

		// calculate image ratio
		$ratio = $width / $height ;
		$destRatio = $destWidth / $destHeight;


		if($ratio > $destRatio){ // initial image is wider than destination
			$multi = $height / $destHeight;
		}elseif($ratio < $destRatio) { // initial image is tighter than the destination
			$multi = $width / $destWidth ;
		}else{
			$multi = $width / $destWidth ; // initial image is homothetic with the destination
		}

		$xcenter = $x + ($width / 2);
		$ycenter = $y + ($height / 2);


		imagecopyresampled($thb, $img, 0, 0, $xcenter-(($destWidth/2)*$multi), $ycenter-(($destHeight/2)*$multi), $destWidth, $destHeight, $destWidth*$multi, $destHeight*$multi);
		// imagecopyresampled($thb, $img, 0, 0, $x, $y, $destWidth, $destHeight, $width, $height);

		return $thb;
	}

	/**
	 * The destination image will be of $destWidth and $destHeight size, but the proportion of the original image might change
	 * @param  integer  $img        The original image (GD image id )
	 * @param  integer  $width      The original image width ( or portion width if $x is given )
	 * @param  integer  $height     The original image height ( or portion height if $x is given )
	 * @param  integer  $destWidth  The destination max width, cant be null
	 * @param  integer  $destHeight The destination max height, cant be null
	 * @param  integer $x          Original image portion left position
	 * @param  integer $y          Original image portion top position
	 * @return integer A new GD image id
	 */
	public function stretch($img,$width,$height,$destWidth,$destHeight,$x=0,$y=0){
		if(is_null($destWidth)||is_null($destHeight)){
			throw new \Smally\Exception('Neither destWidth or destHeight can be null.');
		}

		$thb = imagecreatetruecolor($destWidth,$destHeight);
		imagealphablending($thb, false);
		imagesavealpha($thb, true);

		imagecopyresampled($thb, $img, 0, 0, $x, $y, $destWidth, $destHeight, $width, $height);

		return $thb;
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
			case 'pdf':
			case 'doc':
			case 'docx':
			case 'odt':
			default:
				imagepng($img, $filename);
				break;

		}

		chmod($filename,0777);

		return true;
	}

	/**
	 * Convert color from hexadecimal to rgb
	 * @param  string $hex Hexadecimal color #?([0-9]{3}|[0-9]{6})
	 * @return array RGB : array($r, $g, $b)
	 */
	public function hex2rgb($hex) {
		$hex = str_replace('#', '', $hex);

		if(strlen($hex) == 3) {
			$r = hexdec($hex[0].$hex[0]);
			$g = hexdec($hex[1].$hex[1]);
			$b = hexdec($hex[2].$hex[2]);
		} else {
			$r = hexdec(substr($hex,0,2));
			$g = hexdec(substr($hex,2,2));
			$b = hexdec(substr($hex,4,2));
		}
		return array($r, $g, $b);
	}

	/**
	 * Delete all thumbnails of a particular file
	 * @return boolean
	 */
	public function resetAllThumbnails(){
		$basePath =  dirname($this->_filePath).DIRECTORY_SEPARATOR.'thumbnail';
		return \Smally\Util::rrmdir($basePath);
	}



}