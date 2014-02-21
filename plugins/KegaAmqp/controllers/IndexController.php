<?php


class KegaAmqp_IndexController extends Pimcore_Controller_Action_Admin {
    
    public function indexAction () {

        // reachable via http://your.domain/plugin/KegaAmqp/index/index
//     	
//        $object = Object_Abstract::getById(8);
//        echo '<pre>';
//        print_r($object->getProperties());
//        
//        
//        $object = Object_Test::getById(8);
//        echo '<pre>';
//        print_r($object);
        
        $class = 'Object_Test';
        
//        $xxx = new $class();
//
//        $xxx->setKey('test'.rand());
//        $xxx->setTest('test');
//        $xxx->setParent(Object_Folder::getByPath('/'));
//        $xxx->setPublished(true);
//		$xxx->save();
//
		

		
        die;
//		$exchange = 'fanout_example_exchange';
//		
//
//		$conn = new KegaAmqp_Amqp_Connection('localhost', 5672, 'guest', 'guest', '/');
//		
//		$ch = $conn->channel();
//		$ch->exchange_declare($exchange, 'fanout', false, false, true);
//		
//	
//		$msg = new KegaAmqp_Amqp_Message('Test bericht', array('content_type' => 'text/plain'));
//		$ch->basic_publish($msg, $exchange);
//		
//		$ch->close();
//		$conn->close();
    }
}
