<?php
define('LOGFILE','ACTIVEMQ_DEMO_QUEUES_');
include '/home/itay/dev/emerald/config/bootstrap.php';

/*
 * demo how to use the classes.
 * This will send 100 messages to kuku queue (queue means message is removed once it is read by one client
 * Notice there is at least one underline in the class name, and that the last part MUST match between publisher and subscriber
 */
class SubscriberActiveMQDemo_Kuku extends \Talis\Services\ActiveMQ\Subscriber{
	use \Talis\Services\ActiveMQ\tQueue;
}

class PublisherActiveMQDemo_Kuku extends \Talis\Services\ActiveMQ\Publisher{
	use \Talis\Services\ActiveMQ\tQueue;
}


$r = SubscriberActiveMQDemo_Kuku::get_client(['host'=>'localhost','port'=>'61613']);
$r->listen(function($bob){echo $bob . "\n";});
die;


// Run first the publisher, then run the subscriber.
$p = PublisherActiveMQDemo_Kuku::get_client(['host'=>'localhost','port'=>'61613']);
foreach(range(1,100) as $rbac_user_id){
    $p->publish($rbac_user_id);
}
die;











