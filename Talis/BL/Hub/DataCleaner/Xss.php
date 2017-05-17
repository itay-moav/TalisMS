<?php
class BL_Hub_DataCleaner_Xss extends BL_Hub_DataCleaner_Abstract {
	
	function filter($data) {
		return htmlspecialchars($data);
	}
	
}