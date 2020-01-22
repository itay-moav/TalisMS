the request looks (so u can copy paste and run manually in the ActiveMQ web console)
Anyway, to test this, u need to watch logs.


================================== dependency get fields baba & user exists  ==================================
{"url":"/1/test/dependency/create/user/121/baba/ganush",
 "params":{}
}


================================== dependency get fields baba Missing & user exists ==================================
{"url":"/1/test/dependency/create/user/121",
 "params":{}
}


================================== dependency baba Misspelled & user exists ==================================
{"url":"/1/test/dependency/create/babka/23/user/121",
 "params":{}
}


================================== dependency baba & user missing ==================================
{"url":"/1/test/dependency/create",
 "params":{}
}

============================================================================== START TESTING ====================================================================



<?php
require_once 'bootstrap.php';
$p = talis::get_client();

$dependency1 = "{\"url\":\"/1/test/dependency/create/user/121/baba/ganush\",\"params\":{}}";
$dependency2 = "{\"url\":\"/1/test/dependency/create/user/121\",\"params\":{}}";
$dependency3 = "{\"url\":\"/1/test/dependency/create/babka/23/user/121\",\"params\":{}}";
$dependency4 = "{\"url\":\"/1/test/dependency/create\",\"params\":{}}";


$p->publish($dependency1);
$p->publish($dependency2);
$p->publish($dependency3);
$p->publish($dependency4);


