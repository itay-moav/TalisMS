<?php
function dbg($var){
    \Talis\Logger\MainZim::$CurrentLogger->debug($var);
}
function dbgd($var){
    dbg($var);
    die;
}
function dbgn($n){
    dbg('===================' . $n . '===================');
}
function dbgnd($n){
    dbgn($n);
    die;
}
function dbgr($n,$var){
    dbgn($n);
    dbg($var);
}
function dbgrd($n,$var){
    dbgr($n,$var);
    die;
}
function warning($inp,$full_stack=false) {
    \Talis\Logger\MainZim::$CurrentLogger->warning($inp, $full_stack);
}
function error($inp,$full_stack=false){
    \Talis\Logger\MainZim::$CurrentLogger->error($inp, $full_stack);
}
function fatal($inp,$full_stack=false){
    \Talis\Logger\MainZim::$CurrentLogger->fatal($inp, $full_stack);
}
