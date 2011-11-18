<?php
class filemanager_helpers_FileUtils {

	/**
	 * delete folder and it's content
	 * @param string $dir
	 * @param boolean $recursive
	 * @return boolean true if the folder doesn't exists anymore
	 */
	public static function deleteFolder($dir, $recursive = false){
		 $files = glob($dir. "*", GLOB_MARK);
	    foreach($files as $file){
	        if(substr($file, -1) == '/'){
	        	if($recursive){
					 $this->deleteFolder($file, true);
				}
			}
	        else{
	        	 unlink($file);
	        }
	    }
	    if (is_dir($dir)){
	    	rmdir($dir);
		}
		return !file_exists($dir);
	}

	/**
	 * Check if the path in parameter contains unsafe data
	 * @param string $path
	 * @return boolean true if it's safe
	 */
	public static function securityCheck($path){
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
	
	/**
	 * check if the path is a folder of refPath 
	 * @param string $refPath
	 * @param string $path
	 * @return boolean
	 */
	public static function isFolder($refPath, $path){
		if(!empty($refPath) && !empty($path) && is_string($refPath)){
			do{
				if($refPath == $path || $refPath == basename($path)){
					return true;
				}
				$refPath = dirname($refPath);
			
			} while(($refPath != '/'  && $refPath != '\\' )&& $refPath != '' && $refPath != '.');
		}
		return false;
	}
	
	/**
	 * Clean up the fileName
	 * @param string $fileName
	 * @param string $joker replace denied characted with it
	 * @return the clean filename
	 */
	public static function cleanName($fileName, $joker = '_'){
		$returnValue = '';
		
		$i=0;
		while($i < strlen($fileName)){
			if(preg_match("/^[a-zA-Z0-9.-]{1}$/", $fileName[$i])){
				$returnValue .= $fileName[$i];
			}
			elseif($fileName[$i] != $joker) {
				$returnValue .= $joker;
			}
			$i++;
		}
		return $returnValue;
	}
	
	/**
	 * Concat the path in the array in param
	 * @param array $files
	 * @return string contacted path
	 */
	public static function cleanConcat(array $files){
		$path = '';
		foreach($files as $file){
			if(!preg_match("/^\//", $file) && !preg_match("/\/$/", $path) && !empty($path)){
				$path .= '/';
			}
			$path .= $file;
		}
		return $path;
	}
	
	/**
	 * Get the mime type of the file in parameter
	 * @param string $filename
	 * @return the mime type
	 */
	public static function getMimeType($filename) {
		
		if (empty($filename)) {
			$logger = new Logger('filemanager', Logger::debug_level);
			$logger->error('getMimeType called without filename', __FILE__, __LINE__, 'filemanager');
		}
		

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
			'mp4' => 'video/mp4',
			'wmv' => 'video/x-ms-wmv',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',
        	'ogv' => 'video/ogg',
        	'oga' => 'audio/ogg',

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
        		
        	// srt
            'srt' => 'application/x-subrip'
        );

        
		
		if (function_exists('finfo_open')) {			
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);
            
        }
        else if (function_exists('mime_content_type')) {
        	
			$mimetype = mime_content_type($filename);
		}
		if(!empty($mimetype)){
			if(preg_match("/; charset/", $mimetype)){
				$mimetypeInfos = explode(';', $mimetype);
				$mimetype = $mimetypeInfos[0];
			}
			if(!preg_match("/^application/",$mimetype)){
				return $mimetype;
			}
		}
		
		
		$ext = strtolower(array_pop(explode('.',$filename)));
		
		if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        }
        else {
            return (empty($mimetype)) ? 'application/octet-stream' : $mimetype;
        }
    }
}
?>