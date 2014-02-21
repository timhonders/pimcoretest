<?php 

class Object_Tim extends Object_Concrete {

public $o_classId = 2;
public $o_className = "tim";
public $test;
public $ddd;
public $qqq;


/**
* @param array $values
* @return Object_Tim
*/
public static function create($values = array()) {
	$object = new self();
	$object->setValues($values);
	return $object;
}

/**
* @return string
*/
public function getTest () {
	$preValue = $this->preGetValue("test"); 
	if($preValue !== null && !Pimcore::inAdmin()) { return $preValue;}
	$data = $this->test;
	 return $data;
}

/**
* @param string $test
* @return void
*/
public function setTest ($test) {
	$this->test = $test;
	return $this;
}

/**
* @return string
*/
public function getDdd () {
	$preValue = $this->preGetValue("ddd"); 
	if($preValue !== null && !Pimcore::inAdmin()) { return $preValue;}
	$data = $this->ddd;
	 return $data;
}

/**
* @param string $ddd
* @return void
*/
public function setDdd ($ddd) {
	$this->ddd = $ddd;
	return $this;
}

/**
* @return string
*/
public function getQqq () {
	$preValue = $this->preGetValue("qqq"); 
	if($preValue !== null && !Pimcore::inAdmin()) { return $preValue;}
	$data = $this->qqq;
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

protected static $_relationFields = array (
);

public $lazyLoadedFields = NULL;

}

