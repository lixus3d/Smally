<?php

namespace Smally;

class Uploader {

	protected $_application = null;

	protected $_uploadPath = null;
	protected $_voName = 'Smally\\VO\\Upload';

	protected $_files = array();
	protected $_errors = array();

	protected $_options = array();

	/**
	 * Construct an $uploader object
	 * @param \Smally\Application $application reverse reference to the application
	 */
	public function __construct(\Smally\Application $application){
		$this->setApplication($application);
	}

	/**
	 * Set the application reverse reference
	 * @param \Smally\Application $application Current application linked to this object
	 * @return \Smally\Context
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
	 * Define the base path where to store files
	 * @param string $uploadPath The base path to use for file upload
	 * @return  \Smally\Uploader
	 */
	public function setUploadPath($uploadPath){
		if(is_dir($uploadPath)){
			$this->_uploadPath = $uploadPath;
		}else throw new Exception('Invalid upload path given for setUploadPath : '.$uploadPath);
		return $this;
	}

	/**
	 * Define the upload VO name
	 * @param string $voName The vo name of the object to use for uploaded file, must be an instance of \Smally\Vo\Upload anyway
	 * @return  \Smally\Uploader
	 */
	public function setVoName($voName){
		$this->_voName = $voName;
		return $this;
	}

	public function setOptions($options, $validationKey,$validate=true){

		if($options){
			//var_dump($options);
			$genKey = (int) self::generateOptionsKey($options);
			//echo $genKey;
			$validationKey = (int) $validationKey;
			if( ($validate===false) || ($genKey === $validationKey) ){
				$this->_options = $options;
				return true;
			}
		}
		return false;
	}

	public function setError($error){
		$this->_errors[] = $error;
	}

	/**
	 * Add a file to the uploader
	 * @param \Smally\VO\Upload $upload The upload to add to the uploader
	 */
	public function addFile(\Smally\VO\Upload $upload){
		$this->_files[] = $upload;
		return $this;
	}


	/**
	 * Return the upload base path
	 * @return string
	 */
	public function getUploadPath(){
		return $this->_uploadPath;
	}

	/**
	 * Return the array of upload
	 * @return array Array of \Smally\VO\Upload
	 */
	public function getFiles(){
		return $this->_files;
	}

	public function getErrors(){
		return $this->_errors;
	}

	/**
	 * Get a new Upload VO
	 * @return \Smally\VO\Upload
	 */
	public function getNewVo(){
		$voName = $this->_voName;
		return new $voName();
	}

	static public function generateOptionsKey($options){
		$application = \Smally\Application::getInstance();

		$offset = (string) $application->getConfig()->smally->uploader->offset?:6789;
		$modulo = (string) $application->getConfig()->smally->uploader->modulo?:99999;
		$decay = (string) $application->getConfig()->smally->uploader->decay?:4523;

		$optionsKey = serialize($options);
		//echo $optionsKey.NN;
		$optionsKey = strrev($optionsKey);
		//echo $optionsKey.NN;
		$optionsKey = md5($optionsKey);
		//echo $optionsKey.NN;
		$optionsKey = preg_replace('#[^0-9]#','',$optionsKey);
		//echo $optionsKey.NN;
		$optionsKey = array_sum(str_split($optionsKey))*100000;
		//echo $optionsKey.NN;
		$optionsKey = abs((($optionsKey+$offset)%$modulo)+$decay);
		//echo $optionsKey.NN;
		return $optionsKey;
	}

	/**
	 * Retrieve files to upload from $_FILES
	 * @return null
	 */
	public function retrieveFiles($filesArray=null){
		if(is_null($filesArray)) $filesArray = &$_FILES;
		// We loop on a different files uploaded
		foreach($filesArray as $voName => $files){
			// We extract upload infos
			list($names,$types,$tmp_names,$errors,$sizes) = array_values($files);
			foreach($names as $field => $name){
				$upload = $this->getNewVo();
				$upload->setName($name);
				$upload->filePath = $tmp_names[$field];
				$upload->mimetype = $types[$field];
				$upload->size = $sizes[$field];
				$this->addFile($upload);
			}
		}
	}

	/**
	 * Check if files are acceptable
	 * @return null
	 */
	public function checkFiles(){
		foreach($this->_files as $key => $upload){
			$ok = true;
			switch(true){
				case strpos($upload->name,'.php')!==false:
				case strpos($upload->name,'.php3')!==false:
					$ok = false;
				break;
			}
			if(isset($this->_options)){
				foreach($this->_options as $optionKey => $params){
					switch($optionKey){
						case 'forcename':
							$upload->name = $params.'.'.$upload->getExtension();
						break;
						case 'accept':
							if(!in_array($upload->getExtension(),$params)){
								$this->setError('Ce type de fichier n\'est pas autorisé : '.$upload->getExtension());
								$ok = false;
							}
						break;
						case 'refuse':
							if(in_array($upload->getExtension(),$params)){
								$this->setError('Ce type de fichier n\'est pas autorisé : '.$upload->getExtension());
								$ok = false;
							}
						break;
						case 'count':
							if( count($this->_files) > $params){
								$this->setError('Le nombre de fichiers est dépassé. Nombre de fichier(s) autorisé(s) : '.$params);
								$ok = false;
							}
						break;
					}
				}
			}
			if($ok===false){
				unset($this->_files[$key]);
			}
		}
	}

	/**
	 * Store the files in the database
	 * @return null
	 */
	public function storeFiles(){
		foreach($this->_files as $upload){
			if($upload->getDao()->store($upload)){
				// ... ?
			}else throw new Exception('Unable to store an upload.');
		}
	}

	/**
	 * Move files from their tmp directory to final directory
	 * @return null
	 */
	public function moveFilesFromTemp(){
		foreach($this->_files as $upload){
			if($id = $upload->getId()){
				if($upload->moveFromTemp($this->getUploadPath())){
					// ... ?
				}else throw new Exception('Can\'t move an upload from temp to destination !');
			}
		}
	}

	/**
	 * Execute the uploader logic
	 * @return null
	 */
	public function x(){
		// Retrieve the files to store
		$this->retrieveFiles();
		// Check if they are acceptable
		$this->checkFiles();
		// Store acceptable files to the db
		$this->storeFiles();
		// Move the stored files to their final path
		$this->moveFilesFromTemp();
	}

}