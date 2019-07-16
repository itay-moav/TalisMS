<?php namespace Api;
/**
 * Responsebility: Parses the user input to identify the API class to instantiate
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
        $this->Response->setPayload(\Talis\commons\array_to_object([
            'type'      => 'test',
            'message'   => 'boom',
            'params'    => print_r($this->Request->getAllGetParams(), true),
            'body'      => print_r($this->Request->getBody(), true)
        ]));
        return $this;
    }
}
