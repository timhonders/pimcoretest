<?php


class KegaAmqp_Plugin extends Pimcore_API_Plugin_Abstract implements Pimcore_API_Plugin_Interface {
    
	public static function init() {
		
	}
	
	public static function install (){
        // implement your own logic here
        return true;
	}
	
	public static function uninstall (){
        // implement your own logic here
        return true;
	}

	public static function isInstalled () {
        // implement your own logic here
        return true;
	}
	
	public function postAddObject(Object_Abstract $object){

		

//		$exchange = 'fanout_example_exchange';
//		
//		
//		$msg = new KegaAmqp_Amqp_Message('Test bericht', array('content_type' => 'text/plain'));
//
//		$conn = new KegaAmqp_Amqp_Connection('localhost', 5672, 'guest', 'guest', '/');
//		
//		$ch = $conn->channel();
//		$ch->exchange_declare($exchange, 'fanout', false, false, true);
//		$ch->basic_publish($msg, $exchange);
//		$ch->close();
//		
//		$conn->close();
		
		
	}
	
//	public function postSaveObject(Object_Abstract $object){
//		
//		$exchange = 'fanout_example_exchange';
//		
//		$data = array(
//			'action'=>'object.save',
//			'object' =>array('key'=>$object->getKey())
//		);
//		
//		$msg = new KegaAmqp_Amqp_Message(json_encode($data), array('content_type' => 'text/plain'));
//
//		$conn = new KegaAmqp_Amqp_Connection('localhost', 5672, 'guest', 'guest', '/');
//		
//		$ch = $conn->channel();
//		$ch->exchange_declare($exchange, 'fanout', false, false, true);
//		$ch->basic_publish($msg, $exchange);
//		$ch->close();
//		
//		$conn->close();
//	}
//	
//	public function postUpdateObject(Object_Abstract $object){
//		
//		$exchange = 'fanout_example_exchange';
//		
//		$data = array(
//			'action'=>'object.update',
//			'object' =>array('key'=>print_r($object))
//		);
//		
//		$msg = new KegaAmqp_Amqp_Message(json_encode($data), array('content_type' => 'text/plain'));
//
//		$conn = new KegaAmqp_Amqp_Connection('localhost', 5672, 'guest', 'guest', '/');
//		
//		$ch = $conn->channel();
//		$ch->exchange_declare($exchange, 'fanout', false, false, true);
//		$ch->basic_publish($msg, $exchange);
//		$ch->close();
//		
//		$conn->close();
//	}
//
//	public function postDeleteObject(Object_Abstract $object){
//		
//		$exchange = 'fanout_example_exchange';
//		
//		$data = array(
//			'action'=>'object.delete',
//			'object' =>array('key'=>$object->getKey())
//		);
//		
//		$msg = new KegaAmqp_Amqp_Message(json_encode($data), array('content_type' => 'text/plain'));
//
//		$conn = new KegaAmqp_Amqp_Connection('localhost', 5672, 'guest', 'guest', '/');
//		
//		$ch = $conn->channel();
//		$ch->exchange_declare($exchange, 'fanout', false, false, true);
//		$ch->basic_publish($msg, $exchange);
//		$ch->close();
//		
//		$conn->close();
//	}
}


