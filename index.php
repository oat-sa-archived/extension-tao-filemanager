<?php
/*
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 */
require_once dirname(__FILE__). '/../tao/includes/class.Bootstrap.php';
require_once 'helpers/FileUtils.class.php';

$bootStrap = new BootStrap('filemanager');
$bootStrap->start();
$bootStrap->dispatch();
?>