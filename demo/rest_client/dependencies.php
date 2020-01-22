<?php
include 'bootstrap.php';
echo "\n\n================================== dependency fields baba & user exists  ==================================\n";
$obj=new stdClass;
$obj->params=[];
get_client('/test/dependency/create/user/121/baba/ganush',$obj);

echo "\n\n================================== dependency baba Missing & user exists ==================================\n";
$obj=new stdClass;
$obj->params=[];
get_client('/test/dependency/create/user/121',$obj);

echo "\n\n================================== dependency baba Misspelled & user exists ==================================\n";
$obj=new stdClass;
$obj->params=[];
get_client('/test/dependency/create/babka/23/user/121',$obj);

echo "\n\n================================== dependency baba & user missing ==================================\n";
$obj=new stdClass;
$obj->params=[];
get_client('/test/dependency/create',$obj);