the request looks (so u can copy paste and run manually in the ActiveMQ web console)
Anyway, to test this, u need to watch logs.

{"url":"/test/ping/read",
 "params":{}
}

{"url":"/test/ping/read/param/1",
 "params":{}
}


{"url":"/test/ping/read/more/params/where/added",
 "params":{}
}



============================================================================== START TESTING ====================================================================



<?php
require_once 'bootstrap.php';
$p = talis::get_client();

$dependency1 = "{\"url\":\"/test/ping/read\",\"params\":{}}";
$dependency2ing2 = "{\"url\":\"/test/ping/read/param/1\",\"params\":{}}";
$ping3 = "{\"url\":\"/test/ping/read/more/params/where/added\",\"params\":{}}";

$p->publish($dependency1);
$p->publish($dependency2ing2);
$p->publish($ping3);

