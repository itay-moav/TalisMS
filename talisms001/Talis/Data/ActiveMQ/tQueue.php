<?php
trait Data_ActiveMQ_tQueue{
    protected function type(){
        return Data_ActiveMQ_StompClient::QUEUE;
    }
}