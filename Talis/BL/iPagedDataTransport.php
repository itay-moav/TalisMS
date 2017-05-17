<?php
interface BL_iPagedDataTransport extends BL_iDataTransport{
	public function setPager(Data_APager $Pager);
	/**
	 * @return Data_APager $Pager
	 */
	public function getPager();
}
