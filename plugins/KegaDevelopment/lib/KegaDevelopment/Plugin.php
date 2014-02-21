<?php


class KegaDevelopment_Plugin  extends Pimcore_API_Plugin_Abstract implements Pimcore_API_Plugin_Interface {
    
	public static function install (){
       	
		
		Pimcore_API_Plugin_Abstract::getDb()->query(
			"CREATE TABLE `Kega_development_sqlfiles` (
              `file` varchar(255) COLLATE utf8_bin DEFAULT NULL,
              PRIMARY KEY (`file`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;"
		);
		
		$path = self::getInstallPath();

		if (!is_dir($path)) {
			mkdir($path);
		}
			
		if (self::isInstalled()) {
			return "KegaDevelopment successfully installed.";
		} else {
			return "KegaDevelopment could not be installed";
		}
	}
	
	public static function uninstall (){
        
		Pimcore_API_Plugin_Abstract::getDb()->query("DROP TABLE IF EXISTS `Kega_development_sqlfiles`");
		
		rmdir(self::getInstallPath());

		if (!self::isInstalled()) {
			return "KegaDevelopment successfully uninstalled.";
		} else {
			return "KegaDevelopment could not be uninstalled";
		}
	}
	
	public static function readyForInstall(){
		return true;
	}


	public static function isInstalled(){
		return is_dir(self::getInstallPath());
	}


	public static function getInstallPath(){
		return PIMCORE_PLUGINS_PATH . "/KegaDevelopment/install";
	}
	
	public static function getTranslationFile($language){
		switch ($language) {
			case 'en' :
				return "/KegaDevelopment/texts/en.csv";
			default :
				return null;
		}
	}

	

}
