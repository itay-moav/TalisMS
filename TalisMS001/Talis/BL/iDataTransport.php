<?php
interface BL_iDataTransport {
	public function setFilter(BL_Filter_Abstract $Filter = null);
	public function setData($v);
	public function getData();
	public function addLine($row);
}
