<?php namespace Talis\Services\ActiveMQ;
trait tQueue{
    protected function type(){
        return StompClient::QUEUE;
    }
}
