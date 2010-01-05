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
		$this->setView('index.tpl');
	}
	
	/**
	 * display the list of folders and file of the directory sent in parameter
	 * @return void
	 */
	public function fileData(){
		$root = BASE_DATA;
		$dataDir = urldecode($this->getRequestParameter('dir'));
		if($this->securityCheck($dataDir)){
			$dir = str_replace('//', '/', $root . $dataDir);
			
			$buffer = '';
			if( file_exists($dir) && is_readable($dir)  ) {
				$files = scandir($dir);
				natcasesort($files);
				if( count($files) > 2 ) {
					$buffer .= "<ul class='jqueryFileTree' style='display: none;'>";
					foreach( $files as $file ) {
						if( file_exists($dir . $file) && $file != '.' && $file != '..' && is_dir($dir . $file) ) {
							$buffer .= "<li class='directory collapsed'><a href='#' rel='" . htmlentities($dataDir . $file) . "/'>" . htmlentities($file) . "</a></li>";
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
		}
		echo $buffer;
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
		if($this->hasRequestParameter("file")){
			$file = urldecode($this->getRequestParameter('folder'));
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
	    $files = glob($dir . '*', GLOB_MARK);
	    foreach($files as $file){
	        if(substr($file, -1) == '/'){
	        	 deleteFolder($file);
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
?>