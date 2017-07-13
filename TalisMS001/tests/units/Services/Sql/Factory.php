<?php
use PHPUnit\Framework\TestCase;
//use PHPUnit\DbUnit;
//use PHPUnit\DbUnit\TestCaseTrait;

class Services_Sql_Factory extends TestCase{
	public function testNoConfigConnect(){
		$this->expectException(\LogicException::class);
		Talis\Services\Sql\Factory::getConnectionMySQL('testconn',[]);
	}

	public function testWrongConfigConnect(){
		$this->expectExceptionMessage('SQLSTATE[HY000] [2002] Connection refused');
 		Talis\Services\Sql\Factory::getConnectionMySQL('testconn',[ 'host'=> '168.0.0.1',
                                'database'=>'lms2prod',
                                'username'=>'root',
                                'password'=>'123456!!',
                                'verbosity'=>2
		]);
	}

	public function testConnectionEstablished(){//no error
                $c = Talis\Services\Sql\Factory::getConnectionMySQL('testconn',[ 
				'host'=> '127.0.0.1',
                                'database'=>'lms2prod',
                                'username'=>'root',
                                'password'=>'123456!!',
                                'verbosity'=>2
                ]);

		$res = $c->select("SELECT 'baba'");
		$res = $res->fetchAll()[0]['baba'];
		$this->assertEquals('baba',$res);
		$c->close();
	}
		

}
