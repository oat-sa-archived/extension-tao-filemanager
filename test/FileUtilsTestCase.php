<?php
require_once dirname(__FILE__) . '/../../tao/test/TaoTestRunner.php';
include_once dirname(__FILE__) . '/../includes/raw_start.php';

/**
 * Test Case aiming at testing the filemanager_helpers_FileUtils class.
 * 
 * @author Jerome Bogaerts, <taosupport@tudor.lu>
 * @package filemanager
 * @subpackage test
 */
class FileUtilsTestCase extends UnitTestCase {
    
    public function setUp()
    {		
        parent::setUp();
		TaoTestRunner::initTest();
	}
    
    public function tearDown() {
        parent::tearDown();
    }
    
    public function testGetFolderPath()
    {
    	// We first retrieve the BASE_DATA constant to know where files are
    	// actually stored on this installation.
    	$man = common_ext_ExtensionsManager::singleton();
    	$ext = $man->getExtensionById('filemanager');
    	$base = $ext->getConstant('BASE_DATA');
    	
    	$filePath = $base . 'Animals/puss-in-boots.png';
    	$folderPath = filemanager_helpers_FileUtils::getFolderPath($filePath);
    	$this->assertEqual($folderPath, '/Animals/');
    	
    	// It does not exist!
    	$filePath = $base . 'Animals/hippo-in-boots.png';
    	$folderPath = filemanager_helpers_FileUtils::getFolderPath($filePath);
    	$this->assertNull($folderPath);
    	
    	$filePath = 'C:\\wamp3\\www\\taotrunk\\filemanager\\views\\data\\/Animals/chicken.png';
    	$folderPath = filemanager_helpers_FileUtils::getFolderPath($filePath);
    	$this->assertEqual($folderPath, '/Animals/');
    	
    	// Test a linux like test.
    	$base = '/var/www/taoinstall/filemanager/views/data/';
    	$filePath = $base . 'Animals/puss-in-boots.png';
    	$folderPath = filemanager_helpers_FileUtils::getFolderPath($filePath, $base, false);
    	$this->assertEqual($folderPath, '/Animals/');
    	
    	$filePath = $base . 'puss-in-boots.png';
    	$folderPath = filemanager_helpers_FileUtils::getFolderPath($filePath, $base, false);
    	$this->assertEqual($folderPath, '/');
    }
}
?>