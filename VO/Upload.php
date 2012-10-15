<?php

namespace Smally\VO;

class Upload extends \Smally\VO\Standard {

	public $uploadId = null;
	public $namespace = null;
	public $name = null;
	public $mimetype = null;
	public $size = null;
	public $uid = null;
	public $filePath = null;
	public $utsCreate = null;

	/**
	 * Move the actual Upload from it's uploaded_file directory to it's final path in $dataFolder
	 * @param  string $dataFolder the base path where to store the file
	 * @return boolean true if the move success, false otherwise
	 */
	public function moveFromTemp($dataFolder){

		$inDataFolderPath = $this->generatePath($dataFolder,$this->getUid());

		$destinationFilePath = $dataFolder.$inDataFolderPath.DIRECTORY_SEPARATOR.$this->name;

		if(@move_uploaded_file($this->filePath, $destinationFilePath)){
			$this->filePath = $inDataFolderPath.DIRECTORY_SEPARATOR.$this->name;
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
	 * Generate path for storing
	 * @param  string $dataFolder The base folder of the generated path
	 * @param  string $uid The uid of the path to generate
	 * @return string
	 */
	public function generatePath($dataFolder,$uid){
		$path = $this->cutUid($uid);
		$this->makePath($dataFolder,$path);
		return $path;
	}

	/**
	 * Use makePath to create directory on disk of a $path in $basePath folder
	 * @param  string $basePath The base path where to create the folder of $path
	 * @param  string $path     The path in the $basePath you want to create
	 * @return null
	 */
	public function makePath($basePath,$path){
		$pathParts = explode(DIRECTORY_SEPARATOR,$path);
		$path = $basePath;
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

	public function getExtension(){
		return strtolower(substr(strrchr($this->name,'.'),1));
	}

	public function getReadableSize() {
		$size = $this->size;
	    $mod = 1024;
	    $units = explode(' ','o Ko Mo Go To Po');
	    for ($i = 0; $size > $mod; $i++) {
	        $size /= $mod;
	    }
	    return round($size, 2) . ' ' . $units[$i];
	}

	public function getUrl($type=null,$params=array()){
		$application = \Smally\application::getInstance();
		$url = '';
		switch($type){
			case 'thumbnail':
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

	public function delete(){
		return $this;
	}

	public function toJson(){
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

}