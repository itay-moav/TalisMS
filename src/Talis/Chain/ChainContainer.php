<?php namespace Talis\Chain;

/**
 * Manages chainlinks 
 * 
 * push
 * pop
 * isEmpty
 * debug
 * 
 * @author Itay Moav
 * @date 2021-04-06
 */
class ChainContainer{
    
    /**
     * 
     * @var array
     */
    private array $list_of_chain_links;
    
    /**
     * @param array $list_of_chain_links
     */
    public function __construct(array $list_of_chain_links){
        $this->list_of_chain_links = $list_of_chain_links;
    }

    /**
     * @return aChainLink
     */
    public function pop():array{
        $next_chainlink = array_shift($this->list_of_chain_links);
        return $next_chainlink;
    }

    /**
     * @param array $chainlink
     * @return ChainContainer
     */
    public function push(array $chainlink):ChainContainer{
        $this->list_of_chain_links[] = $chainlink;
        return $this;
    }
    
    /**
     * Clears the chain
     */
    public function clear():void{
        $this->list_of_chain_links=[];
    }
    
    /**
     * @return bool
     */
    public function isEmpty():bool{
        return count($this->list_of_chain_links) === 0;
    }
    
    /**
     */
    public function debug():void{
        foreach($this->list_of_chain_links as $i=>$a_chain_link){
            dbgr("Chain link {$i}",$a_chain_link);
        }
    }
}
