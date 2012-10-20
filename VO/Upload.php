<?php

namespace Smally\VO;

class Upload extends \Smally\VO\Standard {

	protected $_application = null;
	protected $_thumbnailGenerator = null;

	public $uploadId = null;
	public $namespace = null;
	public $name = null;
	public $mimetype = null;
	public $size = null;
	public $uid = null;
	public $filePath = null;
	public $utsCreate = null;

	protected $_filePath = null;
	protected $_dataFolder = null;
	protected $_relativePath = null;

	public function setName($name){
		$this->name = $this->filterName($name);
		return $this;
	}

	public function filterName($name){
		//$name = preg_replace('#[^a-z0-9 \'"`\[\]()+=.,:!_àâäéèëêïîôöùüû-]#i','',$name);
		return $name;
	}

	/**
	 * Set the data folder where the upload is stored
	 * @param string $dataFolder A valid data folder
	 * @return  \Smally\VO\Upload
	 */
	public function setDataFolder($dataFolder){
		if(is_dir($dataFolder)){
			$this->_dataFolder = $dataFolder;
		}else throw new \Smally\Exception('Invalid data folder given.');
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
	 * Return the datafolder path where the current upload is stored
	 * @return string
	 */
	public function getDataFolder(){
		if(is_null($this->_dataFolder)){
			$this->_dataFolder = (string)$this->getApplication()->getConfig()->smally->upload->path?:ROOT_PATH.'public/data/';
		}
		return $this->_dataFolder;
	}

	/**
	 * Get upload uid , generate it if not yet created
	 * @return string
	 */
	public function getUid(){
		if(!$this->uid){
			$this->uid = uniqid();
			$this->getDao()->store($this);
		}
		return $this->uid;
	}

	/**
	 * Return the upload file extension ( from name )
	 * @return string
	 */
	public function getExtension(){
		return strtolower(substr(strrchr($this->name,'.'),1));
	}

	/**
	 * Return the Upload filesize in a human readable format
	 * @return string
	 */
	public function getReadableSize() {
		$size = $this->size;
	    $mod = 1024;
	    $units = explode(' ','o Ko Mo Go To Po');
	    for ($i = 0; $size > $mod; $i++) {
	        $size /= $mod;
	    }
	    return round($size, 2) . ' ' . $units[$i];
	}

	/**
	 * Get an upload url
	 * @param  string $type   The type of url you want , empty for download url
	 * @param  array  $params Some params to give to the url maker
	 * @return string
	 */
	public function getUrl($type=null,$params=array()){
		$application = \Smally\application::getInstance();
		$url = '';
		switch($type){
			case 'thumbnail':
				if($this->filePath){
					$relative = str_replace(DIRECTORY_SEPARATOR,'/',$this->cutUid($this->getUid()));
					$relative .= '/thumbnail/';
					$relative .= $this->getThumbnailGenerator()->constructParamsString($params);
					$relative .= '/'.$this->name;
					$url = $application->urlData($relative);
				}
			break;
			default:
				$url = $application->urlData(str_replace(DIRECTORY_SEPARATOR, '/', $this->filePath));
			break;
			break;
			case 'delete':
				$url = $application->getBaseUrl($application->makeControllerUrl('Upload\\delete',array('id'=>$this->getId())));
			break;
		}
		return $url;
	}

	/**
	 * Return the mime type of the file from it's extension
	 * TODO : Must be greatly improve
	 * @return string
	 */
	public function getMimeType(){
		$extension = $this->getExtension();
		switch($extension){
			case 'jpg':
			case 'jpeg':
				return 'image/jpg';
			case 'png':
			case 'gif':
				return 'image/'.$extension;
			default:
				return 'image/image';
		}
	}

	/**
	 * Get the file relative path ( means without the data folder )
	 * @param  boolean $mkdir Weither to create the path or not
	 * @return string
	 */
	public function getRelativePath($mkdir=false){
		if(is_null($this->_relativePath)){
			$basePath = $this->cutUid($this->getUid());
			if($mkdir){
				$this->makePath($basePath);
			}
			$this->_relativePath = $basePath.DIRECTORY_SEPARATOR.$this->name;
		}
		return $this->_relativePath;
	}

	/**
	 * Get a file path on disk ( complete mean from the current execution directory to the final file, thru the data folder so )
	 * @param  boolean $mkdir Weither to create the path or not
	 * @return string
	 */
	public function getCompletePath($mkdir=false){
		if(is_null($this->_filePath)){
			$this->_filePath = $this->getDataFolder().$this->getRelativePath($mkdir);
		}
		return $this->_filePath;
	}

	/**
	 * Move the actual Upload from it's uploaded_file directory to it's final path in $dataFolder
	 * @param  string $dataFolder the base path where to store the file
	 * @return boolean true if the move success, false otherwise
	 */
	public function moveFromTemp($dataFolder=null){

		if(!is_null($dataFolder)) $this->setDataFolder($dataFolder);

		$destinationFilePath = $this->getCompletePath(true); // true for making directory path if not existing

		if(SMALLY_PLATFORM=='windows') $destinationFilePath = utf8_decode($destinationFilePath); // File will be stored in ISO on windows

		if(@move_uploaded_file($this->filePath, $destinationFilePath)){
			$this->filePath = $this->getRelativePath();
			$this->getDao()->store($this);
			return true;
		}
		return false;
	}

	/**
	 * Cut an Uid in parts for storing
	 * @param  int $uid The uid you want to cut
	 * @param int $cutLen The length that will be cutted , the rest will be the last level
	 * @param  int  $partLen The length of each cutted part, must divide $cutLen
	 * @return string The uid cutted
	 */
	public function cutUid($uid,$cutLen=8,$partLen=4){
		$cutted = '';
		for($i=0;$i<$cutLen;$i+=$partLen){
			$cutted .= substr($uid,$i,$partLen).DIRECTORY_SEPARATOR;
		}
		$cutted .= substr($uid,$i);
		return $cutted;
	}

	/**
	 * Use makePath to create directory on disk of a $path
	 * @param  string $path     The path you want to create
	 * @return null
	 */
	public function makePath($path){
		$pathParts = explode(DIRECTORY_SEPARATOR,$path);
		$path = $this->getDataFolder();
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
	 * Delete the upload ( delete the file so)
	 * @return \Smally\VO\Upload
	 */
	public function delete(){
		unlink($this->getCompletePath());
		return $this;
	}

	/**
	 * Convert the upload to an array format, usually for ajax return
	 * @return array
	 */
	public function toArray(){
		return array(
				'id' => $this->getId(),
				'name' => $this->name,
				'size' => $this->size,
				'readableSize' => $this->getReadableSize(),
				'url' => $this->getUrl(),
				'thumbnail_url' => $this->getUrl('thumbnail'),
				'delete_url' => $this->getUrl('delete'),
				'delete_type' => 'DELETE'
			);
	}

	/**
	 * Get a thumbnail generator for the current upload
	 * @return \Smally\Helper\ThumbnailGenerator
	 */
	public function getThumbnailGenerator(){
		if(is_null($this->_thumbnailGenerator)){
			$this->_thumbnailGenerator = new \Smally\Helper\ThumbnailGenerator();
			$this->_thumbnailGenerator->setFilePath($this->getCompletePath());
		}
		return $this->_thumbnailGenerator;
	}

	/**
	 * Create a thumbnail of the current file and return the created thumbnail path
	 * @param  string $paramsString A parameter string of the format x555-y333-tFill-k34
	 * @return string
	 */
	public function createThumbnail($paramsString=null){

		// We get the ThumbnailGenerator Helper
		return $this->getThumbnailGenerator()
						// We set the generator Params ( from string )
						->setParamsFromString($paramsString)
						// We create the thumbnail
						->create()
						// And finally we return the thumbnail path
						->getThumbnailPath()
						;
	}

	/**
	 * Read a thumbnail file and echo it, default logic also send correct headers ( type, expiration, etc ...)
	 * @param  string  $thumbnailPath The thumbnail to read
	 * @param  boolean $withHeaders   Do we also send the headers ?
	 * @return string
	 */
	public function readThumbnail($thumbnailPath,$withHeaders=true){
		if($withHeaders){
			//header('Content-Type: '.$this->getMimeType());
		}
		echo file_get_contents($thumbnailPath);
		return $this;
	}

}