<?php

namespace Smally\VO;

class Upload extends \Smally\VO\Standard {

	const PRIMARY_KEY = 'uploadId';

	protected $_application = null;
	protected $_thumbnailGenerator = null;

	public $uploadId = null;
	public $name = null;
	public $alt = null;
	public $mimetype = null;
	public $extension = null;
	public $size = null;
	public $uid = null;
	public $filePath = null;
	public $utsCreate = null;

	protected $_filePath = null;
	protected $_dataFolder = null;
	protected $_relativePath = null;

	/**
	 * Set the upload name
	 * @param string $name The name to set for the upload
	 * @return  \Smally\VO\Upload
	 */
	public function setName($name){
		$this->name = $this->filterName($name);
		return $this;
	}

	/**
	 * Change the name of the upload in both name and path
	 * @param  string $name The name you want to set for the file, it will be filtered
	 * @return boolean
	 */
	public function changeName($name){

		// we get old infos
		$oldName = $this->name;
		$oldPath = $this->getCompletePath();

		// we set new info
		$this->name = $this->filterName($name);
		$newPath = $this->getCompletePath(false,true); // false for no making path, true to force regenerate

		if(rename($oldPath,$newPath)){
			chmod($newPath,0777);
			$this->filePath = $this->getRelativePath();
			$this->getDao()->store($this);
			return true;
		}else{
			$this->name = $oldName;
			$this->getCompletePath(false,true);
			return false;
		}
	}

	/**
	 * Regenerate a new path for the upload, update the uid , update the filePath and update the file on disk
	 * @return boolean
	 */
	public function newPath(){

		$actualCompletePath = $this->getCompletePath();
		if(file_exists($actualCompletePath)){

			// update $uid
			$this->getUid(true); // force the creation of a new uid

			// change filePath
			$filename = basename($this->filePath);
			$basePath = $this->cutUid($this->getUid());
			$this->makePath($basePath);
			$newPath = str_replace(array('/','\\'),DIRECTORY_SEPARATOR,$basePath.DIRECTORY_SEPARATOR.$this->name);

			$this->filePath = $newPath;

			// change path on disk
			$newCompletePath = $this->getDataFolder().$newPath;
			if(rename($actualCompletePath,$newCompletePath)){
				$this->store();
				return true;
			}
			return false;
		}

		return null;
	}

	/**
	 * Filter a name to keep only acceptable character
	 * @param  string $name The name to filter
	 * @return string the filtered name
	 */
	public function filterName($name){
		// convert accent
		$name = \Smally\Util::convertAccent($name);
		// convert space, comma, tabulation, etc to '-'
		$name = preg_replace('#[\s\'"`\[\]()\n]#iu','-',$name);
		// list only valid chars
		$name = preg_replace('#[^a-z0-9+._-]#iu','',$name);
		// remove multiple following -
		$name = preg_replace('#-{2,}#iu','-',$name);
		// remove -.,+.,_. entities
		$name = preg_replace('#[-+_]\.#iu','.',$name);
		// trim trailing - and .
		$name = trim($name,'-.');
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
			$this->_dataFolder = (string)$this->getApplication()->getConfig()->smally->upload->path?:PUBLIC_PATH.'data/';
		}
		return $this->_dataFolder;
	}

	/**
	 * Get upload uid , generate it if not yet created
	 * @return string
	 */
	public function getUid($new=false){
		if(!$this->uid||$new){
			$this->uid = uniqid();
			if(!$new){
				$this->getDao()->store($this);
			}
		}
		return $this->uid;
	}

	/**
	 * Return the upload file extension ( direct or from filepath )
	 * @return string
	 */
	public function getExtension(){

		if($this->extension) return $this->extension;

		// try to get it from filePath, if tmp, get it from name
		$ext = strtolower(substr(strrchr($this->filePath,'.'),1));
		if($ext == '' || $ext == 'tmp'){
			$extName = strtolower(substr(strrchr($this->name,'.'),1));
			if($extName !== 'tmp' && preg_match('#^[0-9a-z.-]+$#', $extName)){
				$ext = $extName;
			}
		}
		return $ext;
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
	public function getUploadUrl($type=null,$params=array()){
		$application = \Smally\Application::getInstance();
		$url = '';
		switch($type){
			case 'thumbnail':
				if($this->filePath){
					$relative = str_replace('\\','/',$this->cutUid($this->getUid()));
					$relative .= '/thumbnail/';
					$relative .= $this->getThumbnailGenerator()->constructParamsString($params);
					$cleanFilePath = str_replace('\\','/',$this->filePath);
					$relative .= '/'.substr(strrchr($cleanFilePath,'/'),1); // basename ???
					$url = $application->urlData($relative);
				}
			break;
			case 'delete':
				$url = $application->getBaseUrl($application->makeControllerUrl('Generic\\Upload\\delete',array('id'=>$this->getId())));
			break;
			case 'updatename':
				$url = $application->getBaseUrl($application->makeControllerUrl('Generic\\Upload\\updatename',array('id'=>$this->getId())));
			break;
			case 'updatealt':
				$url = $application->getBaseUrl($application->makeControllerUrl('Generic\\Upload\\updatealt',array('id'=>$this->getId())));
			break;
			default:
				$url = $application->urlData(str_replace('\\', '/', $this->filePath));
			break;
		}
		return $url;
	}

	/**
	 * Return the mime type of the file from it's extension
	 * TODO : Must be greatly improve
	 * @return string
	 */
	public function getMimeTypeThumbnail(){
		$extension = $this->getExtension();
		switch($extension){
			case 'jpg':
			case 'jpeg':
				return 'image/jpeg';
			case 'png':
			case 'gif':
				return 'image/'.$extension;
			case 'pdf':
			case 'doc':
			case 'docx':
			case 'odt':
				return 'image/png';
			default:
				return 'image/image';
		}
	}

	/**
	 * Get the file relative path ( means without the data folder )
	 * @param  boolean $mkdir whether to create the path or not
	 * @return string
	 */
	public function getRelativePath($mkdir=false,$force=false){
		if(is_null($this->_relativePath) || $force){
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
	 * @param  boolean $mkdir whether to create the path or not
	 * @return string
	 */
	public function getCompletePath($mkdir=false,$force=false){
		if(is_null($this->_filePath) || $force ){
			$this->_filePath = $this->getDataFolder().$this->getRelativePath($mkdir,$force);
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

		if(is_uploaded_file($this->filePath)){
			if(@move_uploaded_file($this->filePath, $destinationFilePath)){
				chmod($destinationFilePath,0777);
				$this->filePath = $this->getRelativePath();
				$this->getDao()->store($this);
				return true;
			}
		}elseif( file_exists($this->filePath) ){
			if(@rename($this->filePath, $destinationFilePath)){
				chmod($destinationFilePath,0777);
				$this->filePath = $this->getRelativePath();
				$this->getDao()->store($this);
				return true;
			}
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
	public function toArray($withGetter=true,$withPrimaryKey=true,$excludedValues=array()){
		return array(
				'id' => $this->getId(),
				'name' => $this->name,
				'size' => $this->size,
				'readableSize' => $this->getReadableSize(),
				'url' => $this->getUploadUrl(),
				'thumbnail_url' => $this->getUploadUrl('thumbnail'),
				'delete_url' => $this->getUploadUrl('delete'),
				'updatename_url' => $this->getUploadUrl('updatename'),
				'updatealt_url' => $this->getUploadUrl('updatealt'),
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
			$this->_thumbnailGenerator->setExtension($this->getExtension());
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
			header('Content-Type: '.$this->getMimeTypeThumbnail());
		}
		echo file_get_contents($thumbnailPath);
		return $this;
	}

}