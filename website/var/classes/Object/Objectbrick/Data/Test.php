<?php 

class Object_Objectbrick_Data_Test extends Object_Objectbrick_Data_Abstract  {

public $type = "test";
public $qqq;


/**
* @return string
*/
public function getQqq () {
	$data = $this->qqq;
	if(!$data && Object_Abstract::doGetInheritedValues($this->getObject())) {
		return $this->getValueFromParent("qqq");
	}
	 return $data;
}

/**
* @param string $qqq
* @return void
*/
public function setQqq ($qqq) {
	$this->qqq = $qqq;
	return $this;
}

}

