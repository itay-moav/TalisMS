<?php
function base_url(){
	switch(lifeCycle()){
		case 'itay_development':
			return 'http://192.168.12.148/talis/test';
	}
}
?>
var base_url = "<?=base_url()?>";