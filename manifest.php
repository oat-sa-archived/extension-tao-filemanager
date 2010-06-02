<?php

/**
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 */


	return array(
		'name' => 'FileManager',
		'description' => 'Manage media files on server',
		'additional' => array(
			'version' => '1.2',
			'author' => 'CRP Henry Tudor',
			'dependances' => array(),
			'install' => array( 
				'php' => dirname(__FILE__). '/install/install.php'
			),

			'classLoaderPackages' => array( 
				dirname(__FILE__).'/actions/'
			 )
		)
	);
?>