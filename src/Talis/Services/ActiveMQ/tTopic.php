<?php namespace Talis\Services\ActiveMQ;
trait tTopic{
    protected function type(){
        return StompClient::TOPIC;
    }
}
