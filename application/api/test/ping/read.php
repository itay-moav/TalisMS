<?php namespace Api;
/**
 * Responsebility: Parses the user input to identify the API class to instantiate
 *
 * Notice last element in the chain must implement  \Talis\commons\iRenderable
 * otherwise the response can not be rendered and it will error out after the last chainlink is processed
 * 
 * @author Itay Moav
 * @Date  2017-05-19
 */
class TestPingRead extends \Talis\Chain\aFilteredValidatedChainLink
{

    protected function get_next_bl(): array
    {
        return [
            [Ping::class,[]  ],
            [\Talis\Chain\DoneSuccessfull::class,[] ]
        ];
    }
}

class Ping extends \Talis\Chain\aChainLink
{
    public function process(): \Talis\Chain\aChainLink
    {
        $payload = new \stdClass;
        $payload->type = 'test';
        $payload->message = 'boom';
        $payload->params = print_r($this->Request->getAllGetParams(), true);
        $payload->body = print_r($this->Request->getBody(), true);
        $this->Response->setPayload($payload);
        return $this;
    }
}
