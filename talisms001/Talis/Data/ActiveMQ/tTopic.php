<?php
trait Data_ActiveMQ_tTopic{
    protected function type(){
        return Data_ActiveMQ_StompClient::TOPIC;
    }
}