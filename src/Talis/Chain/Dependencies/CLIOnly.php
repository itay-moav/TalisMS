<?php namespace Talis\Chain\Dependencies;
/**
 * Making sure this is run only via Lord Commander (cli) door
 * 
 * @author Itay Moav
 */
class CLIOnly extends aDependency{
    /**
     *
     * {@inheritDoc}
     * @see \Talis\Chain\Dependencies\ADependency::validate()
     */
    protected function validate():bool{
        $valid = isset($_SERVER['PHP_SELF']) && $_SERVER['PHP_SELF'] === './lord_commander';
        
        \ZimLogger\MainZim::$CurrentLogger->debug('validaror ' . self::class);
        \ZimLogger\MainZim::$CurrentLogger->debug("Am I using CLI door? [{$valid}]");
        
        return $valid;
    }
    
    /**
     * {@inheritDoc}
     * @see \Talis\commons\iRenderable::render()
     */
    public function render(\Talis\commons\iEmitter $emitter):void{
        //\dbgr('RENDER',print_r($this->params,true));
        $response = new \Talis\Message\Response;
        $response->markDependency();
        $response->setMessage("Accessible only via Lord Commander (cli)");
        $response->setStatus(new \Talis\Message\Status\Code403);
        $emitter->emit($response);
    }
}
