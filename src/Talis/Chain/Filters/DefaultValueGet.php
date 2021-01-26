<?php namespace Talis\Chain\Filters;
/**
 * Adds a default value to the GET params, if field does not exists
 *
 */
class DefaultValueGet extends aFilter{
    /**
     * {@inheritDoc}
     * @see \Talis\Chain\Filters\aFilter::filter()
     */
    public function filter(\Talis\Message\Request $Request):void{
	    $field_name     = $this->params['field'];
	    $default        = $this->params['default'];
	    $all_get_params = $this->Request->getAllGetParams();
	    
	    if(!isset($all_get_params[$field_name]) || !$all_get_params[$field_name]){
	       $all_get_params[$field_name] = $default;
	    }
	    
	    $this->Request = new \Talis\Message\Request($Request->getUri(), $all_get_params, $Request->getBody());
	    
	    \ZimLogger\MainZim::$CurrentLogger->debug('FILTERED REQUEST');
	    \ZimLogger\MainZim::$CurrentLogger->debug($this->Request);
	}
}
