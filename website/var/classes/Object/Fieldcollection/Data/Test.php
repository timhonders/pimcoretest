<?php 

class Object_Fieldcollection_Data_Test extends Object_Fieldcollection_Data_Abstract  {

public $type = "test";
public $xx;


/**
* @return string
*/
public function getXx () {
	$data = $this->xx;
	 return $data;
}

/**
* @param string $xx
* @return void
*/
public function setXx ($xx) {
	$this->xx = $xx;
	return $this;
}

}

