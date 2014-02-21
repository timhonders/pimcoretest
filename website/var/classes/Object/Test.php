<?php 

class Object_Test extends Object_Concrete {

public $o_classId = 1;
public $o_className = "test";
public $test;
public $tim;


/**
* @param array $values
* @return Object_Test
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
public function getTim () {
	$preValue = $this->preGetValue("tim"); 
	if($preValue !== null && !Pimcore::inAdmin()) { return $preValue;}
	$data = $this->tim;
	 return $data;
}

/**
* @param string $tim
* @return void
*/
public function setTim ($tim) {
	$this->tim = $tim;
	return $this;
}

protected static $_relationFields = array (
);

public $lazyLoadedFields = NULL;

}

