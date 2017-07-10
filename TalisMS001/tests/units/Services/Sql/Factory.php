<?php
use PHPUnit\Framework\TestCase;
use PHPUnit\DbUnit;
use PHPUnit\DbUnit\TestCaseTrait;

class Services_Sql_Factory extends TestCase{
	public function testConnect(){
		Talis\Services\Sql\Factory::getConnectioniMySQL('testconn',[]);
$this->assertEquals(2, 1 + 1);
	}
}
