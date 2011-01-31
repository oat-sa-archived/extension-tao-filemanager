<?php
/**
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 */
return array(
	'name' => 'filemanager',
	'description' => 'Manage media files on server',
	'additional' => array(
		'version' => '2.0',
		'author' => 'CRP Henry Tudor',
		'dependances' => array('tao'),
		'classLoaderPackages' => array( 
			dirname(__FILE__).'/actions/'
		 )
	)
);
?>