<?php


class KegaDevelopment_IndexController extends Pimcore_Controller_Action_Admin {
    
    public function indexAction () {

        // reachable via http://your.domain/plugin/KegaDevelopment/index/index

    }
    
    public function filesAction () {
    	
    	$files_new = $this->getfiles();
    	
		$response = array(
			'total'=>count($files_new),
			'files'=>$files_new
		);
		
    	echo json_encode($response);
    	die;
    }
    
    public function installAction () {
		
    	$db = Pimcore_Resource::get();
    	$files_new = $this->getfiles();
    	
    	foreach($files_new as $file){
    		
	    	$file = file_get_contents(PIMCORE_DOCUMENT_ROOT . '/website/var/system/'.$file);
	    	$lines = explode("/*--NEXT--*/", $file);
	    	
	    	foreach ($lines as $line){
	    		$db->query($line);
	    	}
    	
    		$sql = "INSERT INTO Kega_development_sqlfiles (file) VALUES ('".$file."');";
    		$db->query($sql);
    	}
    	
    	die;
    }
    
    
    public function getfiles(){
    	
    	$db = Pimcore_Resource::get();
    	$sql = "SELECT file FROM Kega_development_sqlfiles";
    	$files_already_done = array();
    	foreach ($db->fetchAll($sql) as $file){
    		$files_already_done[$file['file']] = $file['file'];
    	}
    	
    	 
    	$files_new = array();
    	if ($handle = opendir(PIMCORE_DOCUMENT_ROOT . '/website/var/system')) {
    		while (false !== ($file = readdir($handle))) {
    			$ext = pathinfo($file, PATHINFO_EXTENSION);
    			if ($ext == 'sql' && !array_key_exists($file, $files_already_done)){ $files_new[filemtime(PIMCORE_DOCUMENT_ROOT . '/website/var/system/' . $file)] = $file; }
    		}
    		closedir($handle);
    	}
    	
    	ksort($files_new);
    	$files_new = array_values($files_new);
    	
    	return $files_new;
    }
}
