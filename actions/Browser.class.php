<?php
/**
 * 
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * @package filemanager
 * @subpackage action
 */
class Browser extends Module {
	
	/**
	 * render the main layout
	 * @return void
	 */
	public function index(){
		
		if($this->hasRequestParameter('openFolder')){
			$folder = $this->getRequestParameter('openFolder');
			if($this->securityCheck($folder)){
				$folder = preg_replace('/^\//', '', $folder);
				$folder = preg_replace('/\/$/', '', $folder);
				$this->setData('openFolder', $folder);
			}
		}
		
		$this->setView('index.tpl');
	}
	
	/**
	 * 
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
				
				if($this->securityCheck($dataDir) && $this->securityCheck($fileName)){
					$destination = str_replace('//', '/', BASE_DATA . $dataDir . $fileName );
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
		if($this->securityCheck($dataDir)){
			$dir = str_replace('//', '/', $root . $dataDir);
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
			natcasesort($files);
			if( count($files) > 2 ) {
				
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
							if($this->isFolder($open, str_replace(BASE_DATA, '', $dir . $file))){
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
	 * check if the path is a folder of refPath 
	 * @param string $refPath
	 * @param string $path
	 * @return boolean
	 */
	private function isFolder($refPath, $path){
		if(!empty($refPath) && !empty($path) && is_string($refPath)){
			do{
				if($refPath == $path){
					return true;
				}
				$refPath = dirname($refPath);
			} while($refPath != '/' && $refPath != '' && $refPath != '.');
		}
		return false;
	}
	
	/**
	 * @todo replace the mime_content_type by the PECL Finfo extension for PHP >= 5.3.0 
	 * @return 
	 */
	public function preview(){ 
		$this->setData('type', '');
		if($this->hasRequestParameter('file')){
			$file = urldecode($this->getRequestParameter('file'));
			if($this->securityCheck($file)){
				
				$path = BASE_DATA . preg_replace("/^\//", '', $file);
				$mimeType = get_mime_type($path);
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
			if($this->securityCheck($dataDir)){
				$data['added'] = mkdir(str_replace('//', '/', BASE_DATA . $dataDir));
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
		if($this->securityCheck($file) && is_readable($file)){
			header("Content-Type: application/force-download");
			header('Content-Disposition: attachment; filename="'.basename($file).'"');
			echo file_get_contents(BASE_DATA . $file);
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
			if($this->securityCheck($file)){
				$data['deleted'] = unlink(BASE_DATA . $file);
			}
		}
		if($this->hasRequestParameter("folder")){
			$folder = urldecode($this->getRequestParameter('folder'));
			if($this->securityCheck($folder)){
				$this->deleteFolder(BASE_DATA . $folder);
				if(!file_exists(BASE_DATA . $folder)){
					$data['deleted'] = true;
				}
			}
		}
		echo json_encode($data);
	}
	
	/**
	 * delete folder recursively
	 * @param string $dir
	 * @return void
	 */
	private  function deleteFolder($dir) {
	    $files = glob($dir. "*", GLOB_MARK);
	    foreach($files as $file){
	        if(substr($file, -1) == '/'){
	        	 $this->deleteFolder($file);
	        }
	        else{
	        	 unlink($file);
	        }
	    }
	    if (is_dir($dir)){
	    	rmdir($dir);
		}
	} 
	
	
	/**
	 * Check if the path in parameter contains unsafe data
	 * @param object $path
	 * @return boolean true if it's safe
	 */
	protected function securityCheck($path){
		//security check: detect directory traversal (deny the ../)
		if(preg_match("/\.\.\//", $path)){
			return false;
		}
		
		//security check:  detect the null byte poison by finding the null char injection
		for($i = 0; $i < strlen($path); $i++){
			if(ord($path[$i]) === 0){
				return false;
			}
		}
		return true;
	} 
}

 function get_mime_type($filename) {

        $mime_types = array(

            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        $ext = strtolower(array_pop(explode('.',$filename)));
        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        }
        elseif (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);
            return $mimetype;
        }
        else {
            return 'application/octet-stream';
        }
    }
?>