<?php 

class Object_Test extends Object_Concrete {

public $o_classId = 1;
public $o_className = "test";
public $test;
public $xxx;


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
public function getXxx () {
	$preValue = $this->preGetValue("xxx"); 
	if($preValue !== null && !Pimcore::inAdmin()) { return $preValue;}
	$data = $this->xxx;
	 return $data;
}

/**
* @param string $xxx
* @return void
*/
public function setXxx ($xxx) {
	$this->xxx = $xxx;
	return $this;
}

protected static $_relationFields = array (
);

public $lazyLoadedFields = NULL;

}

