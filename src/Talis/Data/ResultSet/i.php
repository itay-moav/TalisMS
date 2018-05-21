<?php namespace Talis\Data\ResultSet;
interface i extends \Iterator{
	public function setData($v);
	public function getData();
	public function addLine($row);
	public function setPager(\Talis\Data\aPager $Pager):i;
	public function getPager():?\Talis\Data\aPager;
	public function setFilter($QueryFilter);
	public function getFilter();
}
