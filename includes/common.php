<?php
/*
 * 
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * 
 */
session_start();
require_once 	dirname(__FILE__). "/config.php";
require_once 	dirname(__FILE__). "/../helpers/FileUtils.class.php";

set_include_path(get_include_path() . PATH_SEPARATOR . GENERIS_BASE_PATH.'/..');

$GLOBALS['lang']	= $GLOBALS['default_lang'];

//we are in a TAO CONTEXT and authentication is not bypassed
if(class_exists("tao_helpers_Context", true) && !NO_FM_AUTH){
	
	include_once 'tao/includes/constants.php'; 
	
	//initialize the contexts
	tao_helpers_Context::load('TAO_MODE');
	
	//Authentication and API initialization
	$userService = tao_models_classes_ServiceFactory::get('tao_models_classes_UserService');
	$userService->connectCurrentUser();
	
	
	
	//initialize I18N
	if(Session::hasAttribute('ui_lang')){
		$uiLang = Session::getAttribute('ui_lang') ;
	}
	else{
		$currentUser = $userService->getCurrentUser();
		$uiLg = null;
		if($currentUser){
			$uiLg  = $currentUser->getOnePropertyValue(new core_kernel_classes_Property(PROPERTY_USER_UILG));
		}
		(!is_null($uiLg)) ? $uiLang = $uiLg->getLabel() : $uiLang = $GLOBALS['default_lang'];
		
	}
	tao_helpers_I18n::init($uiLang);
}
?>