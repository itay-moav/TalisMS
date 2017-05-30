<?php
include 'bootstrap.php';
echo "\n\n================================== filter does nothing, missing the params (should fail)  ==================================\n";
$obj=new stdClass;
$obj->params=[];
get_client('/1/test/filter/read',$obj);

echo "\n\n================================== filter does nothing, has the params  ==================================\n";
$obj=new stdClass;
$obj->params=['shubi'=>'dubi'];
get_client('/1/test/filter/read',$obj);

echo "\n\n================================== filter has the field, but not the value to modify ->does nothing  ==================================\n";
$obj=new stdClass;
$obj->params=['mumble'=>'dubi'];
get_client('/1/test/filter/read',$obj);

echo "\n\n================================== filter has the field and the value -> modifies it to brumbrum  ==================================\n";
$obj=new stdClass;
$obj->params=['mumble'=>'blabla'];
get_client('/1/test/filter/read',$obj);

