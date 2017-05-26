<?php
include 'bootstrap.php';
echo "\n\n================================== PING  ==================================\n";
$obj=new stdClass;
$obj->params=[];
get_client('/1/test/ping/read',$obj);


echo "\n\n================================== PING  /i/x/b/1/f/5 ==================================\n";
$obj=new stdClass;
$obj->params=[];
get_client('/1/test/ping/read/i/x/b/1/f/5',$obj);