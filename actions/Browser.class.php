<?php
/**
 * 
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * @package filemanager
 * @subpackage action
 */
class Browser extends Module {
	
	public function index(){
		$this->setData('test', 'this is a test');
		$this->setView('index.tpl');
	}
	
	/**
	 * display the list of folders and file of the directory sent in parameter
	 * @return void
	 */
	public function fileData(){
		$root = BASE_DATA;
		
		$dataDir = urldecode($this->getRequestParameter('dir'));
		
		//security check: detect directory traversal (deny the ../)
		if(preg_match("/\.\.\//", $dataDir)){
			throw new Exception("Security failure: directory path not allowed");
		}
		
		//security check:  detect the null byte poison by finding the null char injection
		for($i = 0; $i < strlen($dataDir); $i++){
			if(ord($dataDir[$i]) === 0){
				throw new Exception("Security failure: directory path not allowed");
			}
		}
		
		$dir = str_replace('//', '/', $root . $dataDir);
		
		$buffer = '';
		if( file_exists($dir) && is_readable($dir)  ) {
			$files = scandir($dir);
			natcasesort($files);
			if( count($files) > 2 ) {
				$buffer .= "<ul class='jqueryFileTree' style='display: none;'>";
				foreach( $files as $file ) {
					if( file_exists($dir . $file) && $file != '.' && $file != '..' && is_dir($dir . $file) ) {
						$buffer .= "<li class='directory collapse'><a href='#' rel='" . htmlentities($dataDir . $file) . "/'>" . htmlentities($file) . "</a></li>";
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
		echo $buffer;
	}
}
?>