<?php
/**
 * 
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * @package filemanager
 * @subpackage action
 */
class Browser extends Module {
	
	public function __construct(){
		$errorMessage = __('Access denied. Please renew your authentication!');
		if(class_exists("tao_helpers_Context", true) && !NO_FM_AUTH){
			if(!core_kernel_users_Service::singleton()->isASessionOpened()){
				if(tao_helpers_Request::isAjax()){
					header("HTTP/1.0 403 Forbidden");
					echo $errorMessage;
					return;
				}
				throw new Exception($errorMessage);
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
			if(FileUtils::securityCheck($folder)){
				$folder = preg_replace('/^\//', '', $folder);
				$folder = preg_replace('/\/$/', '', $folder);
				$this->setData('openFolder', $folder);
			}
		}
		
		$this->setView('index.tpl');
	}
	
	/**
	 * Manage the form file upload
	 * @return void
	 */
	public function fileUpload(){
		$parameters = '';
		if(is_array($_FILES['media_file'])){
			$copy = true;
			if(!isset($_FILES['media_file']['type'])){
				$copy = false;
			}
			else if(!in_array($_FILES['media_file']['type'], $GLOBALS['allowed_media']) || !$_FILES['media_file']['type']){
				$copy = false;
			}
			if(!isset($_FILES['media_file']['size'])){
				$copy = false;
			}
			else if( $_FILES['media_file']['size'] > UPLOAD_MAX_SIZE || !is_int($_FILES['media_file']['size'])){
				$copy = false;
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
				
				if(FileUtils::securityCheck($dataDir) && FileUtils::securityCheck($fileName)){
					$destination = FileUtils::cleanConcat(array(BASE_DATA, $dataDir, $fileName));
					if(move_uploaded_file($_FILES['media_file']['tmp_name'], $destination)){
						$parameters = "?openFolder=$dataDir";
					}
				}
			}
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
		if(FileUtils::securityCheck($dataDir)){
			$dir = FileUtils::cleanConcat(array($root, $dataDir));
			$buffer = $this->createFolderList($dir, $dataDir, $openDir);
		}
		echo $buffer;
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
							if(FileUtils::isFolder($open, str_replace(BASE_DATA, '', $dir . $file))){
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
			if(FileUtils::securityCheck($file)){
				
				$path = FileUtils::cleanConcat(array(BASE_DATA, $file));
				
				$mimeType = FileUtils::getMimeType($path);
				if(in_array($mimeType, $GLOBALS['allowed_media'])){
					if(file_exists($path) && is_readable($path)){
						$this->setData('mime_type', $mimeType);
						$this->setData('source', URL_DATA . $file);
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
			if(FileUtils::securityCheck($dataDir)){
				$data['added'] = mkdir(FileUtils::cleanConcat(array(BASE_DATA, $dataDir)));
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
		if(FileUtils::securityCheck($file) && is_readable($file)){
			header("Content-Type: application/force-download");
			header('Content-Disposition: attachment; filename="'.basename($file).'"');
			echo file_get_contents(FileUtils::cleanConcat(array(BASE_DATA, $file)));
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
			if(FileUtils::securityCheck($file)){
				$data['deleted'] = unlink(FileUtils::cleanConcat(array(BASE_DATA, $file)));
			}
		}
		if($this->hasRequestParameter("folder")){
			$folder = urldecode($this->getRequestParameter('folder'));
			if(FileUtils::securityCheck($folder)){
				if(FileUtils::deleteFolder(FileUtils::cleanConcat(array(BASE_DATA, $folder)), true)){
					$data['deleted'] = true;
				}
			}
		}
		echo json_encode($data);
	}
	
	
}
?>