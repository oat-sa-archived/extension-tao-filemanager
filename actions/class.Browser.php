<?php
/**
 * 
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * @package filemanager
 * @subpackage action
 */
class filemanager_actions_Browser extends Module {
	
	public function __construct(){
		$errorMessage = __('Access denied. Please renew your authentication!');
		if(class_exists("tao_helpers_Context", true) && !NO_FM_AUTH){
			if(!core_kernel_users_Service::singleton()->isASessionOpened()){
				throw new tao_models_classes_UserException($errorMessage);
			}		
		}
	}
	
	/**
	 * render the main layout
	 * @return void
	 */
	public function index(){
		
		if($this->hasRequestParameter('openFolder')){
			$folder = $this->getRequestParameter('openFolder');
			if(filemanager_helpers_FileUtils::securityCheck($folder)){
				$folder = preg_replace('/^\//', '', $folder);
				$folder = preg_replace('/\/$/', '', $folder);
				$this->setData('openFolder', $folder);
			}
		}
		if($this->hasRequestParameter('urlData')){
			$this->setData('urlData', $this->getRequestParameter('urlData'));
		}
		if($this->hasRequestParameter('error')){
			$this->setData('error', $this->getRequestParameter('error'));
		}
		
		$this->setData('upload_limit', $this->getFileUploadLimit());
		
		$this->setView('index.tpl');
	}
	
	private function getFileUploadLimit($pInMegabytes = false) {
		
		function tobytes($val) {
			$val = trim($val);
			$last = strtolower($val[strlen($val)-1]);
			switch($last) {
				// The 'G' modifier is available since PHP 5.1.0
				case 'g':
					$val *= 1024;
				case 'm':
					$val *= 1024;
				case 'k':
					$val *= 1024;
			}
		
			return $val;
		}
		
		$max_upload		= tobytes(ini_get('upload_max_filesize'));
		$max_post		= tobytes(ini_get('post_max_size'));
		$memory_limit	= tobytes(ini_get('memory_limit'));
		
		$limit = min($max_upload, $max_post, $memory_limit,UPLOAD_MAX_SIZE);
		return $pInMegabytes ? round(($limit / 1048576), 1) : $limit;
	}
	
	/**
	 * Manage the form file upload
	 * @return void
	 */
	public function fileUpload(){
		
		$error = '';
		
		$parameters = '';
		
		if(is_array($_FILES['media_file'])){
			
			$copy = true;
			if($_FILES['media_file']['error']  !== UPLOAD_ERR_OK) {
				
				$logger = new Logger('filemanager', Logger::debug_level);
				$logger->debug('fileUpload failed with Error '.$_FILES['media_file']['error'], __FILE__, __LINE__, 'filemanager');
				
				$copy = false;
				switch ($_FILES['media_file']['error']) {
					case UPLOAD_ERR_INI_SIZE:
					case UPLOAD_ERR_FORM_SIZE:
						$error = __('media size must be less than : ').$this->getFileUploadLimit(true).__(' MB');
						break;
					case UPLOAD_ERR_PARTIAL:
						$error = __('file upload failed');
						break;
					case UPLOAD_ERR_NO_FILE:
						$error = __('no file uploaded');
						break;
				}
			} else {
			
				if(!isset($_FILES['media_file']['type'])){
					$copy = false;
				}
				elseif(empty($_FILES['media_file']['type'])){
					$_FILES['media_file']['type'] = filemanager_helpers_FileUtils::getMimeType($_FILES['media_file']['tmp_name']);
				}
				if(!$_FILES['media_file']['type'] || !in_array($_FILES['media_file']['type'], $GLOBALS['allowed_media'])){
					$copy = false;
					$error = __('unknow media type : '.$_FILES['media_file']['type']);
				}
				if(!isset($_FILES['media_file']['size'])){
					$copy = false;
					$error = __('unknow media size');
				}
				else if( $_FILES['media_file']['size'] > UPLOAD_MAX_SIZE || !is_int($_FILES['media_file']['size'])){
					$copy = false;
					$error = __('media size must be less than : ').$this->getFileUploadLimit(true).__(' MB');
				}
			}
			
			if($copy){
				if($this->hasRequestParameter('media_folder')){
					$dataDir = urldecode($this->getRequestParameter('media_folder'));
				}
				else{
					$dataDir = "/";
				}
				if($this->hasRequestParameter('media_name')){
					$fileName = basename($this->getRequestParameter('media_name'));
				}
				else{
					$fileName = $_FILES['media_file']['name'];
				}
				
				if(filemanager_helpers_FileUtils::securityCheck($dataDir) && filemanager_helpers_FileUtils::securityCheck($fileName)){
					$fileName = filemanager_helpers_FileUtils::cleanName($fileName);
					$destination = filemanager_helpers_FileUtils::cleanConcat(array(BASE_DATA, $dataDir, $fileName));
					if(move_uploaded_file($_FILES['media_file']['tmp_name'], $destination)){
						$parameters = "?openFolder=$dataDir&urlData=$fileName";
					}
					else{
						$error = __('unable to move uploaded file');
					}
				}
				else{
					$error = __('Security issue');
				}
			}
		} else {
			$logger = new Logger('filemanager', Logger::debug_level);
			$logger->debug('file upload information missing, probably file > upload limit in php.ini', __FILE__, __LINE__, 'filemanager');
			
			$error = __('media size must be less than : ').$this->getFileUploadLimit(true).__(' MB');
		}
		if(!empty($error)){
			if(strpos($parameters, '?') === false){
				$parameters .= '?';
			}
			else{
				$parameters .= '&';
			}
			$parameters .= 'error='.$error;
		}
		$this->redirect("index".$parameters);
	}
	
	/**
	 * display the list of folders and file of the directory sent in parameter
	 * @return void
	 */
	public function fileData(){
		$root = BASE_DATA;
		$dataDir = urldecode($this->getRequestParameter('dir'));
		$openDir = false;
		if($this->hasRequestParameter('open')){
			$openDir = urldecode($this->getRequestParameter('open'));
			if($openDir == "null"){
				$openDir = false;
			}
		}
		$buffer = '';
		if(filemanager_helpers_FileUtils::securityCheck($dataDir)){
			$dir = filemanager_helpers_FileUtils::cleanConcat(array($root, $dataDir));
			$buffer = $this->createFolderList($dir, $dataDir, $openDir);
		}
		echo $buffer;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Module::getData()
	 */
	public function getInfo(){
		$response = array();
		if($this->hasRequestParameter('file')){
			$file = urldecode($this->getRequestParameter('file'));
			if(filemanager_helpers_FileUtils::securityCheck($file)){
				$path = filemanager_helpers_FileUtils::cleanConcat(array(BASE_DATA, $file));
				$mimeType = filemanager_helpers_FileUtils::getMimeType($path);
				if(in_array($mimeType, $GLOBALS['allowed_media'])){
					if(file_exists($path) && is_readable($path)){
						
						$width = $height = '';
						if(preg_match("/^image/", $mimeType)){
							$this->setData('isImage', true);
							$size = getimagesize($path);
							$width = $size[0];
							$height = $size[1];
						}
						
						$response['width'] = $width;
						$response['height'] = $height;
						$response['type'] = $mimeType;
					}
				}
			}
		}
		print json_encode($response);
	}
	
	/**
	 * Create an HTML list from a folder tree
	 * @param string $dir
	 * @param string $dataDir
	 * @param mixed $open [optional]
	 * @param boolean $recursive [optional]
	 * @return string the list
	 */
	private function createFolderList($dir, $dataDir, $open = false, $recursive = false){
		if(!preg_match("/\/$/", $dir)){
			$dir .= '/';
		}
		$buffer = '';
		if( file_exists($dir) && is_readable($dir)  ) {
			$files = scandir($dir);
			foreach( $files as $i => $file ) {
				if(preg_match("/^\./", $file)){
					unset($files[$i]);
				}
			}
			natcasesort($files);
			if( count($files) > 0 ) {
				
				if($recursive){
					$buffer .= "<ul  class='jqueryFileTree' style='display: block;'>";
				}
				else{
					$buffer .= "<ul class='jqueryFileTree' style='display: none;'>";
				}
				
				foreach( $files as $file ) {
					if( file_exists($dir . $file) && $file != '.' && $file != '..' && is_dir($dir . $file) ) {
					 
					 	$tmpbuffer = '';
					 	$status = 'collapsed';
						if($open !== false){
							if(filemanager_helpers_FileUtils::isFolder($open, str_replace(BASE_DATA, '', $dir . $file))){
								$tmpbuffer = $this->createFolderList($dir . $file, preg_replace("/\/$/", '', $dataDir) . '/' .  preg_replace("/\/$/", '', $file).'/', $open, true);
								$status = 'expanded';
							}
						}
						$buffer .= "<li class='directory $status'><a href='#' rel='" . htmlentities($dataDir . $file) . "/'>" . htmlentities($file) . "</a>$tmpbuffer</li>";
					}
				}
				foreach( $files as $file ) {
					if( file_exists($dir . $file) && $file != '.' && $file != '..' && !is_dir($dir . $file) ) {
						$ext = preg_replace('/^.*\./', '', $file);
						$buffer .= "<li class='file ext_$ext'><a href='#' rel='" . htmlentities($dataDir . $file) . "'>" . htmlentities($file) . "</a></li>";
					}
				}
				$buffer .= "</ul>";	
			}
		}
		
		return $buffer;
	}
	
	
	/**
	 * @todo replace the mime_content_type by the PECL Finfo extension for PHP >= 5.3.0 
	 * @return 
	 */
	public function preview(){ 
		$this->setData('type', '');
		if($this->hasRequestParameter('file')){
			$file = urldecode($this->getRequestParameter('file'));
			if(filemanager_helpers_FileUtils::securityCheck($file)){
				
				$path = filemanager_helpers_FileUtils::cleanConcat(array(BASE_DATA, $file));
				
				$mimeType = filemanager_helpers_FileUtils::getMimeType($path);
				if(in_array($mimeType, $GLOBALS['allowed_media'])){
					
					if(file_exists($path) && is_readable($path)){
						
						$source = URL_DATA . $file;
						$width = $height = 140;
						$this->setData('isImage', false);
						$this->setData('isEmbded', false);
						if(preg_match("/^image/", $mimeType)){
							$this->setData('isImage', true);
							$size = getimagesize(BASE_DATA . $file);
							$width = $size[0];
							$height = $size[1];
							
							if($height > 200){
								$height = 200;
								$width 	= '';
							}
						}
						
						$this->setData('width', $width);
						$this->setData('height', $height);
						$this->setData('mime_type', $mimeType);
						$this->setData('source', $source);
					
					}
				}
			}
		}
		$this->setView("preview.tpl");
	}
	
	/**
	 * Create a new folder into the given directory 
	 * @return void
	 */
	public function addFolder(){
		$data = array('added' => false);
		
		try{
			$parentDir = urldecode($this->getRequestParameter('parent'));
			$folder = urldecode($this->getRequestParameter('folder'));
			
			$dataDir = $parentDir . $folder;
			if(filemanager_helpers_FileUtils::securityCheck($dataDir)){
				$data['added'] = mkdir(filemanager_helpers_FileUtils::cleanConcat(array(BASE_DATA, $dataDir)));
			}
		
		} catch(Exception $e){ }
		echo json_encode($data);
	}
	
	/**
	 * Download the file in paramteters
	 * @return void
	 */
	public function download(){
		$file = urldecode($this->getRequestParameter('file'));
		if(filemanager_helpers_FileUtils::securityCheck($file) && is_readable($file)){
			header("Content-Type: application/force-download");
			header('Content-Disposition: attachment; filename="'.basename($file).'"');
			echo file_get_contents(filemanager_helpers_FileUtils::cleanConcat(array(BASE_DATA, $file)));
			return;
		}
		$this->redirect("index");
	}
	
	/**
	 * delete the selected file or folder
	 * @return void
	 */
	public function delete(){
		$data = array('deleted' => false);
		if($this->hasRequestParameter('file')){
			$file = urldecode($this->getRequestParameter('file'));
			if(filemanager_helpers_FileUtils::securityCheck($file)){
				$data['deleted'] = unlink(filemanager_helpers_FileUtils::cleanConcat(array(BASE_DATA, $file)));
			}
		}
		if($this->hasRequestParameter("folder")){
			$folder = urldecode($this->getRequestParameter('folder'));
			if(filemanager_helpers_FileUtils::securityCheck($folder)){
				if(filemanager_helpers_FileUtils::deleteFolder(filemanager_helpers_FileUtils::cleanConcat(array(BASE_DATA, $folder)), true)){
					$data['deleted'] = true;
				}
			}
		}
		echo json_encode($data);
	}
	
	
}
?>